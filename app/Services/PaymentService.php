<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Repositories\PaymentRepository;

class PaymentService
{
    public function __construct(
        protected PaymentRepository $paymentRepository
    ) {}

    public function createForOrder(Order $order, array $data): Payment
    {
        return $this->paymentRepository->create([
            'order_id'       => $order->id,
            'payment_method' => $data['payment_method'],
            'amount'         => $data['amount'],
            'status'         => 'pending',
        ]);
    }
}
