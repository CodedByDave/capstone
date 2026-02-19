<?php

namespace App\Services;

use App\Models\Order;
use App\Repositories\OrderRepository;

class OrderService
{
    public function __construct(
        protected OrderRepository $orderRepository
    ) {}

    public function create(array $data): Order
    {
        // Calculate total_price from the modules array
        $total = collect($data['modules'])->sum('price');

        return $this->orderRepository->create(array_merge($data, [
            'total_price' => $total,
            'status'      => 'pending',
        ]));
    }
}
