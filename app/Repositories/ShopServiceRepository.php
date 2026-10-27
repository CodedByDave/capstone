<?php

namespace App\Repositories;

use App\Models\ShopService;
use Illuminate\Database\Eloquent\Collection;

class ShopServiceRepository
{
    public function allByShop(int $shopId, bool $activeOnly = false, bool $onlyTrashed = false): Collection
    {
        return ShopService::query()
            ->with('supplies:id,name,unit')
            ->where('shop_id', $shopId)
            ->when($activeOnly, fn ($q) => $q->where('is_active', true))
            ->when($onlyTrashed, fn ($q) => $q->onlyTrashed())
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

    /** @param list<array{inventory_id: int, quantity: float|int|string, unit: string}> $supplies */
    public function syncSupplies(ShopService $service, array $supplies): void
    {
        $service->supplies()->sync(
            collect($supplies)->mapWithKeys(fn (array $line) => [
                (int) $line['inventory_id'] => ['quantity' => $line['quantity'], 'unit' => $line['unit']],
            ])->all(),
        );
    }

    /** @return Collection<int, ShopService> Active services of the shop with the supplies each one uses. */
    public function activeWithSupplies(int $shopId): Collection
    {
        return ShopService::query()->where('shop_id', $shopId)->where('is_active', true)->with('supplies:id,name,unit')->get();
    }

    public function delete(ShopService $service): void
    {
        $service->delete();
    }
}
