<?php

namespace App\Repositories;

use App\Models\InventoryCategory;
use Illuminate\Database\Eloquent\Collection;

class InventoryCategoryRepository extends Repository
{
    public function __construct(InventoryCategory $inventoryCategory)
    {
        parent::__construct($inventoryCategory);
    }

    public function allForShop(int $shopId): Collection
    {
        return $this->query()
            ->where('shop_id', $shopId)
            ->orderBy('name')
            ->get();
    }

    // Must call onlyTrashed() on the model class directly
    // because $this->query() excludes soft-deleted records
    public function archivedForShop(int $shopId): Collection
    {
        return InventoryCategory::onlyTrashed()
            ->where('shop_id', $shopId)
            ->orderBy('name')
            ->get();
    }

    public function createForShop(int $shopId, array $data): InventoryCategory
    {
        return $this->create([...$data, 'shop_id' => $shopId]);
    }

    public function restore(int $id): bool
    {
        return InventoryCategory::withTrashed()->findOrFail($id)->restore();
    }
}
