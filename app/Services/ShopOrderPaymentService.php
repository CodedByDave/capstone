<?php

namespace App\Services;

use App\Exceptions\ShopOrderPaymentException;
use App\Models\Shop;
use App\Models\ShopOrder;
use App\Models\User;
use App\Repositories\ShopOrderRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ShopOrderPaymentService
{
    public function __construct(
        private readonly ShopOrderRepository $orderRepository,
        private readonly ShopPaymentSettingsService $settingsService,
        private readonly PaymongoService $paymongoService,
    ) {}

    public function initiate(User $customer, string $paymentPublicId, string $paymentMethod): string
    {
        $order = $this->ownedOrder($customer, $paymentPublicId);

        if ($order->payment_status === 'paid') {
            throw new ShopOrderPaymentException('This order has already been paid.');
        }

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

        // Charge only what is still owed, e.g. a delivery fee added after the laundry was paid.
        if ($order->balanceDue() <= 0) {
            throw new ShopOrderPaymentException('The order must have a payable amount before checkout.');
        }

        if ($order->balanceDue() < 100) {
            throw new ShopOrderPaymentException('Order total must be at least ₱100 to pay through PayMongo.');
        }

        $order = $this->orderRepository->update($order, [
            'payment_method' => $paymentMethod,
            'paymongo_session_id' => null,
        ]);

        $session = $this->paymongoService->createShopOrderSession(
            $order,
            $this->secretKeyFor($order->shop),
        );
        $this->orderRepository->update($order, [
            'paymongo_session_id' => $session['data']['id'],
        ]);

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
            'amount' => $order->balanceDue(),
            'shop_name' => $order->shop->shop_name,
            'session_id' => $order->paymongo_session_id,
        ];
    }

    public function verifyPaymongoReturn(User $customer, string $paymentPublicId): bool
    {
        $order = $this->ownedOrder($customer, $paymentPublicId);

        if ($order->actual_weight_kg === null) {
            throw new ShopOrderPaymentException(
                'Payment cannot be completed until the shop records the actual laundry weight.',
            );
        }

        if ($order->payment_status === 'paid') {
            return true;
        }

        if (! $order->shop || ! $order->shop->hasPaymongo() || ! $order->paymongo_session_id) {
            throw new ShopOrderPaymentException('Could not verify payment. Please contact the shop.');
        }

        $session = $this->paymongoService->getShopOrderSession(
            $order->paymongo_session_id,
            $this->secretKeyFor($order->shop),
        );

        $sessionStatus = $session['data']['attributes']['status'] ?? null;
        $paymentStatus = $session['data']['attributes']['payment_intent']['attributes']['status'] ?? null;

        if (! in_array($sessionStatus, ['completed', 'paid'], true)
            && ! in_array($paymentStatus, ['paid', 'succeeded'], true)) {
            return false;
        }

        $paymentId = $this->paymongoService->extractPaymentId($session);

        DB::transaction(fn () => $this->orderRepository->update($order, [
            'payment_status' => 'paid',
            'amount_paid' => $order->total_amount,
            'paid_at' => now(),
            'paymongo_payment_id' => $paymentId,
        ]));

        Log::info('ShopOrder paid via PayMongo', ['order_id' => $order->id, 'payment_id' => $paymentId]);

        return true;
    }

    public function assertOwnership(User $customer, string $paymentPublicId): void
    {
        $this->ownedOrder($customer, $paymentPublicId);
    }

    public function receiptData(User $customer, string $paymentPublicId): array
    {
        $order = $this->orderRepository->findForCustomerReceipt($paymentPublicId, $customer->id)
            ?? throw (new ModelNotFoundException)->setModel(ShopOrder::class);

        if ($order->payment_status !== 'paid' || ! $order->paid_at) {
            throw new ShopOrderPaymentException('A receipt is available only after payment is completed.');
        }

        return [
            'order_id' => $order->id,
            'payment_public_id' => $order->payment_public_id,
            'order_number' => $order->order_number,
            'receipt_number' => 'INV-'.$order->order_number,
            'payment_reference' => $order->paymongo_payment_id,
            'payment_method' => $order->payment_method,
            'paid_at' => $order->paid_at->toIso8601String(),
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
            'discount_amount' => (float) $order->discount_amount,
            'delivery_fee' => (float) $order->delivery_fee,
            'total_amount' => (float) $order->total_amount,
            'amount_paid' => (float) $order->amount_paid,
            'is_demo' => $order->shop
                ? $this->settingsService->usesDemoCredentials($order->shop)
                : false,
        ];
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
