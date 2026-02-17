<?php

namespace App\Services;

use App\Models\Order;
use App\Repositories\OrderRepository;

class OrderService
{
    public function __construct(protected OrderRepository $orders) {}

    public function create(array $data): Order
    {
        $data['status'] = 'pending';
        $data['branch_name'] = $data['branch_name'] ?: 'N/A';
        $data['total_price'] = array_reduce(
            $data['modules'] ?? [],
            fn($sum, $module) => $sum + ($module['price'] ?? 0),
            0
        );

        return $this->orders->create($data);
    }

    public function markPaid(Order $order): bool
    {
        return $this->orders->markAsPaid($order);
    }
}
