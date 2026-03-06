<?php

namespace App\Services;

use App\Models\Order;
use App\Repositories\OrderRepository;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        protected OrderRepository $orderRepository
    ) {}

    public function create(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            $total = collect($data['modules'])->sum('price');

            $order = $this->orderRepository->create(array_merge($data, [
                'total_price' => $total,
                'status'      => 'pending',
            ]));

            foreach ($data['modules'] as $module) {
                $order->modules()->create([
                    'name'  => $module['name'],
                    'price' => $module['price'],
                ]);
            }

            return $order->load('modules');
        });
    }
}
