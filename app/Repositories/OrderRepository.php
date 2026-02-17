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
}
