<?php

namespace App\Repositories;

use App\Models\ShopService;
use Illuminate\Database\Eloquent\Collection;

class ShopServiceRepository
{
    public function allByShop(int $shopId, bool $activeOnly = false, bool $onlyTrashed = false): Collection
    {
        return ShopService::query()
            ->where('shop_id', $shopId)
            ->when($activeOnly, fn($q) => $q->where('is_active', true))
            ->when($onlyTrashed, fn($q) => $q->onlyTrashed())
            ->orderBy('service_name')
            ->get();
    }

    public function find(int $id): ShopService
    {
        return ShopService::findOrFail($id);
    }

    public function create(array $data): ShopService
    {
        return ShopService::create($data);
    }

    public function update(ShopService $service, array $data): ShopService
    {
        $service->update($data);
        return $service->fresh();
    }

    public function delete(ShopService $service): void
    {
        $service->delete();
    }
}
