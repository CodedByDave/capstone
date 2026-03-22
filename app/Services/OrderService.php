<?php

namespace App\Services;

use App\Models\Order;
use App\Repositories\OrderRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        protected OrderRepository $orderRepository
    ) {}

    // ── Existing ──────────────────────────────────────────────────────────────

    public function create(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            $baseTotal = collect($data['modules'])->sum('price');

            $total = match ($data['subscription_plan'] ?? 'monthly') {
                'annually' => $baseTotal * 0.90 * 12,
                default    => $baseTotal,
            };

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

    // ── Admin

    public function getPaginated(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->orderRepository->getPaginated($filters, $perPage);
    }

    public function getStats(): array
    {
        return $this->orderRepository->getStats();
    }

    public function find(int $id): Order
    {
        return $this->orderRepository->findWithRelations($id);
    }
}
