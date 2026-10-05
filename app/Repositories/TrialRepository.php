<?php

namespace App\Repositories;

use App\Models\Order;

class TrialRepository
{
    public function hasOpenApplication(int $userId): bool
    {
        return Order::query()
            ->where('user_id', $userId)
            ->whereIn('status', ['approved', 'paid', 'pending'])
            ->exists();
    }

    public function hasUsedTrial(int $userId): bool
    {
        return Order::query()
            ->where('user_id', $userId)
            ->where('is_trial', true)
            ->exists();
    }

    public function create(array $data, array $modules): Order
    {
        $order = Order::query()->create($data);

        foreach ($modules as $moduleName) {
            $order->modules()->create(['name' => $moduleName, 'price' => 0]);
        }

        return $order->load('modules');
    }
}
