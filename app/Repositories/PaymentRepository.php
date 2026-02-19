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
            'status'  => 'paid',
            'paid_at' => now(),
        ], $extra));
    }
}
