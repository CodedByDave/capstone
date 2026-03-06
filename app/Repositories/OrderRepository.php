<?php

namespace App\Repositories;

use App\Models\Order;

class OrderRepository extends Repository
{
    public function __construct(Order $order)
    {
        parent::__construct($order);
    }

    public function markAsPaid(Order $order): bool
    {
        return $this->update($order, ['status' => 'paid']);
    }

    public function create(array $data): Order
    {
        return Order::create(collect($data)->except('modules')->toArray());
    }
}
