<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Repositories\PaymentRepository;

class PaymentService
{
    public function __construct(protected PaymentRepository $payments) {}

    public function createForOrder(Order $order, array $data): Payment
    {
        return $this->payments->create([
            'order_id' => $order->id,
            'payment_method' => $data['payment_method'],
            'amount' => $data['amount'],
            'status' => 'pending'
        ]);
    }

    public function markPaid(Payment $payment, array $extra = []): bool
    {
        return $this->payments->markAsPaid($payment, $extra);
    }
}
