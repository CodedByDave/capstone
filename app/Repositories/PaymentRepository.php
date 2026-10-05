<?php

namespace App\Repositories;

use App\Models\Payment;

class PaymentRepository extends Repository
{
    public function __construct(Payment $payment)
    {
        parent::__construct($payment);
    }

    public function markAsPaid(Payment $payment, array $extra = []): bool
    {
        return $this->update($payment, array_merge([
            'status' => 'paid',
            'paid_at' => now(),
        ], $extra));
    }

    public function setPaymongoSessionId(Payment $payment, string $sessionId): void
    {
        $this->update($payment, ['paymongo_session_id' => $sessionId]);
    }

    public function findForOrder(int $orderId): ?Payment
    {
        return Payment::query()->where('order_id', $orderId)->first();
    }
}
