<?php

namespace App\Repositories;

use App\Models\ShopServicePricing;
use Illuminate\Database\Eloquent\Collection;

class ShopServiceRepository
{
    public function allByShop(int $shopId, bool $activeOnly = false, bool $onlyTrashed = false): Collection
    {
        return ShopServicePricing::query()
            ->where('shop_id', $shopId)
            ->when($activeOnly, fn($q) => $q->where('is_active', true))
            ->when($onlyTrashed, fn($q) => $q->onlyTrashed())
            ->orderBy('service_name')
            ->get();
    }

    public function find(int $id): ShopServicePricing
    {
        return ShopServicePricing::findOrFail($id);
    }

    public function create(array $data): ShopServicePricing
    {
        return ShopServicePricing::create($data);
    }

    public function update(ShopServicePricing $service, array $data): ShopServicePricing
    {
        $service->update($data);
        return $service->fresh();
    }

    public function delete(ShopServicePricing $service): void
    {
        $service->delete();
    }
}