<?php

namespace App\Repositories;

use App\Models\ServiceCategory;
use App\Models\ShopService;
use Illuminate\Database\Eloquent\Collection;

class ServiceCategoryRepository
{
    public function allByShop(int $shopId): Collection
    {
        return ServiceCategory::query()->where('shop_id', $shopId)->orderBy('name')->get();
    }

    /** @return list<string> */
    public function keysByShop(int $shopId): array
    {
        return ServiceCategory::query()->where('shop_id', $shopId)->pluck('key')->all();
    }

    public function findByKey(int $shopId, string $key): ?ServiceCategory
    {
        return ServiceCategory::query()->where('shop_id', $shopId)->where('key', $key)->first();
    }

    public function keyExists(int $shopId, string $key): bool
    {
        return ServiceCategory::query()->where('shop_id', $shopId)->where('key', $key)->exists();
    }

    public function nameExists(int $shopId, string $name): bool
    {
        return ServiceCategory::query()->where('shop_id', $shopId)->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->exists();
    }

    public function create(array $data): ServiceCategory
    {
        return ServiceCategory::create($data);
    }

    public function delete(ServiceCategory $category): void
    {
        $category->delete();
    }

    /** Services of the shop (archived ones too) that still use the category. */
    public function servicesUsing(int $shopId, string $key): int
    {
        return ShopService::withTrashed()->where('shop_id', $shopId)->where('category', $key)->count();
    }
}
