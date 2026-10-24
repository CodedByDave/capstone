<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Repositories\OrderRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\ShopRepository;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function __construct(
        protected PaymentRepository $paymentRepository,
        protected OrderRepository $orderRepository,
        protected ShopRepository $shopRepository,
        protected PaymongoService $paymongoService,
    ) {}

    public function initiateApprovedOrderPayment(User $user, string $paymentMethod): array
    {
        [$order, $payment] = DB::transaction(function () use ($user, $paymentMethod) {
            $order = $this->orderRepository->findLatestApprovedPayableForUser($user->id);
            $this->orderRepository->setPaymentMethod($order, $paymentMethod);

            $payment = $this->createForOrder($order, [
                'payment_method' => $paymentMethod,
                'amount' => $order->total_price,
            ]);

            return [$order, $payment];
        });

        $session = $this->paymongoService->createCheckoutSession($order);
        $sessionId = $session['data']['id'];
        $this->paymentRepository->setPaymongoSessionId($payment, $sessionId);

        return [
            'order_id' => $order->id,
            'checkout_url' => $session['data']['attributes']['checkout_url'],
        ];
    }

    public function createForOrder(Order $order, array $data): Payment
    {
        return $this->paymentRepository->create([
            'order_id' => $order->id,
            'payment_method' => $data['payment_method'],
            'amount' => $data['amount'],
            'status' => 'pending',
        ]);
    }

    public function verifiedReceipt(User $user, Order $routeOrder): array
    {
        if ($routeOrder->user_id !== $user->id) {
            throw new AuthorizationException;
        }

        $order = $this->orderRepository->findWithModules($routeOrder->id);
        $payment = $this->paymentRepository->findForOrder($order->id);

        if ($payment?->paymongo_session_id) {
            try {
                $session = $this->paymongoService->getCheckoutSession($payment->paymongo_session_id);
                $sessionStatus = $session['data']['attributes']['status'] ?? null;
                $paymentStatus = $session['data']['attributes']['payment_intent']['attributes']['status'] ?? null;

                if (in_array($sessionStatus, ['completed', 'paid'], true)
                    || in_array($paymentStatus, ['paid', 'succeeded'], true)) {
                    $this->completeVerifiedPayment(
                        $order,
                        $payment,
                        $this->paymongoService->extractPaymentId($session),
                    );
                    $order = $this->orderRepository->refreshWithModules($order);
                }
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        return $this->receiptData($order, $payment);
    }

    public function historyFor(User $user, array $filters): array
    {
        $payments = $this->paymentRepository->paidHistoryForUser($user->id, $filters);
        $payments->through(fn (Payment $payment) => [
            'id' => $payment->id,
            'transaction_reference' => $payment->order->transaction_reference,
            'order_public_id' => $payment->order->public_id,
            'shop_name' => $payment->order->shop_name,
            'plan_name' => $payment->order->plan_name,
            'billing_months' => $payment->order->billing_months,
            'payment_method' => $payment->payment_method,
            'amount' => (float) $payment->amount,
            'paid_at' => $payment->paid_at?->toIso8601String(),
            'expires_at' => $payment->order->expires_at?->toIso8601String(),
        ]);

        return [
            'payments' => $payments,
            'summary' => $this->paymentRepository->paidSummaryForUser($user->id),
            'filters' => [
                'search' => $filters['search'] ?? '',
                'payment_method' => $filters['payment_method'] ?? '',
                'sort_by' => $filters['sort_by'] ?? 'paid_at',
                'sort_direction' => $filters['sort_direction'] ?? 'desc',
            ],
        ];
    }

    public function historicalReceipt(User $user, Order $routeOrder): array
    {
        if ($routeOrder->user_id !== $user->id) {
            throw new AuthorizationException;
        }

        $payment = $this->paymentRepository->findPaidForOrder($routeOrder->id);

        if (! $payment) {
            abort(404);
        }

        $order = $this->orderRepository->findWithModules($routeOrder->id);

        return $this->receiptData($order, $payment);
    }

    private function completeVerifiedPayment(Order $order, Payment $payment, ?string $paymongoPaymentId): void
    {
        if ($payment->status === 'paid' && $order->status === 'paid') {
            return;
        }

        DB::transaction(function () use ($order, $payment, $paymongoPaymentId) {
            $this->paymentRepository->markAsPaid($payment, [
                'paymongo_payment_id' => $paymongoPaymentId,
            ]);
            $this->orderRepository->markPaidWithExpiry($order);
            $this->shopRepository->syncFromApprovedOrder($order);

            // A first paid plan also ends the free trial it replaces.
            if ($order->is_upgrade || ! $order->is_trial) {
                $this->orderRepository->expireOtherActiveSubscriptions($order);
            }
        });
    }

    private function receiptData(Order $order, ?Payment $payment = null): array
    {
        return [
            'public_id' => $order->public_id,
            'transaction_reference' => $order->transaction_reference,
            'status' => $payment?->status === 'paid' ? 'paid' : $order->status,
            'shop_name' => $order->shop_name,
            'owner_name' => $order->owner_name,
            'email' => $order->email,
            'phone' => $order->phone,
            'block_street' => $order->block_street,
            'municipality' => $order->municipality,
            'barangay' => $order->barangay,
            'postal_code' => $order->postal_code,
            'total_price' => $payment?->amount ?? $order->total_price,
            'payment_method' => $payment?->payment_method ?? $order->payment_method,
            'paid_at' => $payment?->paid_at,
            'paymongo_payment_id' => $payment?->paymongo_payment_id,
            'plan_name' => $order->plan_name,
            'billing_months' => $order->billing_months,
            'expires_at' => $order->expires_at,
            'created_at' => $order->created_at,
            'modules' => $order->modules->map(fn ($module) => [
                'name' => $module->name,
                'price' => $module->price,
            ]),
        ];
    }
}
