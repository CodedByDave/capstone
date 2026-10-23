<?php

namespace App\Services;

use App\Exceptions\ShopOrderPaymentException;
use App\Models\Shop;
use App\Models\ShopOrder;
use App\Models\ShopOrderPayment;
use App\Models\User;
use App\Repositories\OrderPaymentRepository;
use App\Repositories\ShopOrderRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/** Paying a shop order online through PayMongo, in full or a part at a time. */
class ShopOrderPaymentService
{
    /** PayMongo cannot charge less than this. */
    public const MINIMUM_ONLINE_PAYMENT = 100.0;

    /** A checkout that was not completed within this many hours is given up on. */
    public const PENDING_EXPIRY_HOURS = 24;

    public function __construct(
        private readonly ShopOrderRepository $orderRepository,
        private readonly OrderBillingBreakdownService $billing,
        private readonly OrderPaymentRepository $payments,
        private readonly OrderPaymentService $ledger,
        private readonly ShopPaymentSettingsService $settingsService,
        private readonly PaymongoService $paymongoService,
        private readonly ShopContext $shopContext,
    ) {}

    /**
     * Start an online checkout for the full balance, or for $amount when the customer pays in parts.
     * Returns the PayMongo checkout URL.
     */
    public function initiate(User $customer, string $paymentPublicId, string $paymentMethod, ?float $amount = null): string
    {
        $order = $this->ownedOrder($customer, $paymentPublicId);

        if ($order->status === 'cancelled') {
            throw new ShopOrderPaymentException('A cancelled order cannot be paid.');
        }

        if ($order->actual_weight_kg === null) {
            throw new ShopOrderPaymentException(
                'Payment will be available after the shop records the actual laundry weight and final amount.',
            );
        }

        if (! $order->shop || ! $order->shop->hasPaymongo()) {
            throw new ShopOrderPaymentException('Online payment is not set up for this shop.');
        }

        // A checkout the customer already finished may not have been noticed yet; count it first
        // so they are never asked to pay the same money twice.
        $this->settlePendingPayments($order);
        $order = $this->ownedOrder($customer, $paymentPublicId);

        if ($order->payment_status === 'paid') {
            throw new ShopOrderPaymentException('This order has already been paid.');
        }

        // Charge only what is still owed, e.g. a delivery fee added after the laundry was paid.
        $balance = $order->balanceDue();

        if ($balance <= 0) {
            throw new ShopOrderPaymentException('The order must have a payable amount before checkout.');
        }

        $charge = $amount === null ? $balance : round($amount, 2);

        if ($charge > $balance) {
            throw new ShopOrderPaymentException('The amount is more than the remaining balance of ₱'.number_format($balance, 2).'.');
        }

        if ($charge < self::MINIMUM_ONLINE_PAYMENT) {
            throw new ShopOrderPaymentException(
                $amount === null
                    ? 'Order total must be at least ₱100 to pay through PayMongo.'
                    : 'The smallest amount you can pay online is ₱100.',
            );
        }

        // The checkout offers the method the customer just picked; it is saved with the payment below.
        $order->payment_method = $paymentMethod;

        $session = $this->paymongoService->createShopOrderSession(
            $order,
            $this->secretKeyFor($order->shop),
            $charge,
        );
        $sessionId = $session['data']['id'];

        DB::transaction(function () use ($order, $paymentMethod, $charge, $sessionId, $customer) {
            $locked = $this->orderRepository->lockForUpdate($order);

            // Only the newest checkout stays open.
            foreach ($this->payments->pendingForOrder($locked->id) as $older) {
                $this->payments->update($older, ['status' => ShopOrderPayment::STATUS_CANCELLED]);
            }

            $this->payments->create([
                'shop_order_id' => $locked->id,
                'shop_id' => $locked->shop_id,
                'amount' => $charge,
                'method' => $paymentMethod,
                'status' => ShopOrderPayment::STATUS_PENDING,
                'source' => ShopOrderPayment::SOURCE_ONLINE,
                'recorded_by' => $customer->id,
                'paymongo_session_id' => $sessionId,
            ]);

            $this->orderRepository->update($locked, [
                'payment_method' => $paymentMethod,
                'paymongo_session_id' => $sessionId,
            ]);
        });

        return $session['data']['attributes']['checkout_url'];
    }

    public function demoCheckoutData(User $customer, string $paymentPublicId): array
    {
        $order = $this->ownedDemoOrder($customer, $paymentPublicId);

        return [
            'id' => $order->id,
            'payment_public_id' => $order->payment_public_id,
            'order_number' => $order->order_number,
            'customer_name' => $order->customer_name,
            'amount' => $this->payments->latestPendingForOrder($order->id)?->amount ?? $order->balanceDue(),
            'shop_name' => $order->shop->shop_name,
            'session_id' => $order->paymongo_session_id,
        ];
    }

    /**
     * The customer came back from PayMongo. Returns the order's payment status ('paid' or 'partial')
     * once money has been received, or null while the payment is still processing.
     */
    public function verifyPaymongoReturn(User $customer, string $paymentPublicId): ?string
    {
        $order = $this->ownedOrder($customer, $paymentPublicId);

        if ($order->actual_weight_kg === null) {
            throw new ShopOrderPaymentException(
                'Payment cannot be completed until the shop records the actual laundry weight.',
            );
        }

        $pending = $this->payments->latestPendingForOrder($order->id);

        if (! $pending) {
            return $order->amount_paid > 0 ? $order->payment_status : null;
        }

        if (! $order->shop || ! $order->shop->hasPaymongo()) {
            throw new ShopOrderPaymentException('Could not verify payment. Please contact the shop.');
        }

        if ($this->settle($pending, $order->shop) !== 'confirmed') {
            return null;
        }

        $order = $this->ownedOrder($customer, $paymentPublicId);
        Log::info('ShopOrder payment received via PayMongo', ['order_id' => $order->id, 'payment' => $pending->public_id]);

        return $order->payment_status;
    }

    /** The customer asks to check a payment that may have gone through. */
    public function checkForCustomer(User $customer, string $paymentPublicId): string
    {
        $order = $this->ownedOrder($customer, $paymentPublicId);

        return $this->settlePendingPayments($order);
    }

    /** A shop user asks to check the online payments waiting on one of their orders. */
    public function reconcileForShop(User $actor, ShopOrder $order): string
    {
        if ($order->shop_id !== $this->shopContext->shopIdFor($actor)) {
            throw (new ModelNotFoundException)->setModel(ShopOrder::class);
        }

        return $this->settlePendingPayments($order->loadMissing('shop'));
    }

    /**
     * Check every online payment that has been waiting a while, as a safety net for customers who
     * never returned to the site. Returns how many were confirmed and how many expired.
     *
     * @return array{confirmed: int, still_pending: int, expired: int, failed: int}
     */
    public function reconcileAllPending(): array
    {
        $result = ['confirmed' => 0, 'still_pending' => 0, 'expired' => 0, 'failed' => 0];

        $due = $this->payments->pendingOnlineBetween(
            now()->subMinute(),
            now()->subHours(self::PENDING_EXPIRY_HOURS),
        );

        foreach ($due as $payment) {
            try {
                $shop = $payment->order?->shop;

                if (! $shop || ! $shop->hasPaymongo()) {
                    $result['failed']++;

                    continue;
                }

                $outcome = $this->settle($payment, $shop);
                $result[$outcome === 'confirmed' ? 'confirmed' : 'still_pending']++;
            } catch (\Throwable $exception) {
                $result['failed']++;
                Log::warning('Could not reconcile a pending online payment', [
                    'payment' => $payment->public_id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        $result['expired'] = $this->payments->expirePendingBefore(now()->subHours(self::PENDING_EXPIRY_HOURS));

        return $result;
    }

    /** The customer backed out of PayMongo's checkout. */
    public function cancelPending(User $customer, string $paymentPublicId): void
    {
        $order = $this->ownedOrder($customer, $paymentPublicId);

        // They may have paid in another tab, so look before giving up on it.
        if ($this->settlePendingPayments($order) === 'confirmed') {
            return;
        }

        foreach ($this->payments->pendingForOrder($order->id) as $payment) {
            $this->ledger->cancelPending($payment);
        }
    }

    public function assertOwnership(User $customer, string $paymentPublicId): void
    {
        $this->ownedOrder($customer, $paymentPublicId);
    }

    public function receiptData(User $customer, string $paymentPublicId): array
    {
        $order = $this->orderRepository->findForCustomerReceipt($paymentPublicId, $customer->id)
            ?? throw (new ModelNotFoundException)->setModel(ShopOrder::class);

        $confirmed = $this->payments->confirmedForOrder($order->id);

        if ($confirmed->isEmpty()) {
            throw new ShopOrderPaymentException('A receipt is available only after payment is completed.');
        }

        $last = $confirmed->last();

        return [
            'order_id' => $order->id,
            'payment_public_id' => $order->payment_public_id,
            'order_number' => $order->order_number,
            'receipt_number' => 'INV-'.$order->order_number,
            'payment_reference' => $last->paymongo_payment_id,
            'payment_method' => $last->method,
            'paid_at' => ($last->paid_at ?? $last->created_at)->toIso8601String(),
            'customer_name' => $order->customer_name,
            'customer_phone' => $order->customer_phone,
            'shop' => [
                'name' => $order->shop?->shop_name,
                'phone' => $order->shop?->phone,
                'address' => collect([
                    $order->shop?->block_street,
                    $order->shop?->barangay,
                    $order->shop?->municipality,
                    $order->shop?->postal_code,
                ])->filter()->implode(', '),
            ],
            'service_name' => $order->service?->service_name ?? 'Laundry service',
            'weight_kg' => (float) ($order->actual_weight_kg ?? $order->estimated_weight_kg ?? 0),
            'additional_charges' => (float) $order->additional_charges,
            ...$this->billing->additionalChargeDetail($order),
            'discount_amount' => (float) $order->discount_amount,
            'delivery_fee' => (float) $order->delivery_fee,
            'total_amount' => (float) $order->total_amount,
            'amount_paid' => (float) $order->amount_paid,
            'balance_due' => $order->balanceDue(),
            'is_fully_paid' => $order->payment_status === 'paid',
            'payments' => $confirmed->map(fn (ShopOrderPayment $payment) => [
                'amount' => (float) $payment->amount,
                'method' => $payment->method,
                'paid_at' => ($payment->paid_at ?? $payment->created_at)->toIso8601String(),
            ])->values()->all(),
            'is_demo' => $order->shop
                ? $this->settingsService->usesDemoCredentials($order->shop)
                : false,
        ];
    }

    /**
     * Ask PayMongo about each open checkout of the order and count the ones that were paid.
     * Returns 'confirmed' when any was paid, 'pending' while some are still open, otherwise 'none'.
     */
    private function settlePendingPayments(ShopOrder $order): string
    {
        $pending = $this->payments->settleCandidatesForOrder($order->id);

        if ($pending->isEmpty()) {
            return 'none';
        }

        $shop = $order->shop;

        if (! $shop || ! $shop->hasPaymongo()) {
            return 'pending';
        }

        $outcome = 'pending';

        foreach ($pending as $payment) {
            try {
                if ($this->settle($payment, $shop) === 'confirmed') {
                    $outcome = 'confirmed';
                }
            } catch (\Throwable $exception) {
                Log::warning('Could not check an online payment', [
                    'payment' => $payment->public_id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        return $outcome;
    }

    /** Check one pending payment with PayMongo and confirm it when it was paid. */
    private function settle(ShopOrderPayment $payment, Shop $shop): string
    {
        // Placeholder sessions from the old demo flow never exist at PayMongo.
        if (! $payment->paymongo_session_id || str_starts_with($payment->paymongo_session_id, 'cs_demo_')) {
            return 'pending';
        }

        $session = $this->paymongoService->getShopOrderSession(
            $payment->paymongo_session_id,
            $this->secretKeyFor($shop),
        );

        $sessionStatus = $session['data']['attributes']['status'] ?? null;
        $paymentStatus = $session['data']['attributes']['payment_intent']['attributes']['status'] ?? null;

        if (! in_array($sessionStatus, ['completed', 'paid'], true)
            && ! in_array($paymentStatus, ['paid', 'succeeded'], true)) {
            return 'pending';
        }

        $this->ledger->confirmOnline($payment, $this->paymongoService->extractPaymentId($session));

        return 'confirmed';
    }

    private function secretKeyFor(Shop $shop): string
    {
        if (! $this->settingsService->usesDemoCredentials($shop)) {
            return (string) $shop->paymongo_secret_key;
        }

        $platformKey = (string) config('services.paymongo.secret_key');

        if (! str_starts_with($platformKey, 'sk_test_')) {
            throw new ShopOrderPaymentException(
                'LaundryHub demo checkout requires a configured PayMongo test secret key.',
            );
        }

        return $platformKey;
    }

    private function ownedDemoOrder(User $customer, string $paymentPublicId): ShopOrder
    {
        $order = $this->ownedOrder($customer, $paymentPublicId);

        if (! $order->shop
            || ! $this->settingsService->usesDemoCredentials($order->shop)
            || ! str_starts_with((string) $order->paymongo_session_id, 'cs_demo_')) {
            throw new ShopOrderPaymentException('This order does not have an active demo payment session.');
        }

        return $order;
    }

    private function ownedOrder(User $customer, string $paymentPublicId): ShopOrder
    {
        return $this->orderRepository->findForCustomerOnlinePayment($paymentPublicId, $customer->id)
            ?? throw (new ModelNotFoundException)->setModel(ShopOrder::class);
    }
}
