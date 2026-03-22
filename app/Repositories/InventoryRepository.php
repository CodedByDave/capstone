<?php

namespace App\Repositories;

use App\Models\Inventory;
use App\Models\Shop;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class InventoryRepository extends Repository
{
    public function __construct(Inventory $inventory)
    {
        parent::__construct($inventory);
    }

    public function paginateForShop(
        int $shopId,
        int $perPage = 15,
        array $filters = []
    ): LengthAwarePaginator {
        $query = $this->query()
            ->where('shop_id', $shopId)
            ->with(['category', 'supplier'])
            ->orderBy('name');

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('sku', 'like', "%{$filters['search']}%");
            });
        }

        if (!empty($filters['category_id'])) {
            $query->where('inventory_categories_id', $filters['category_id']);
        }

        if (!empty($filters['supplier_id'])) {
            $query->where('supplier_id', $filters['supplier_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['stock']) && $filters['stock'] === 'low') {
            $query->whereColumn('quantity', '<=', 'min_stock');
        }

        return $query->paginate($perPage);
    }

    public function allForShop(int $shopId): Collection
    {
        return $this->query()
            ->where('shop_id', $shopId)
            ->with(['category', 'supplier'])
            ->orderBy('name')
            ->get();
    }

    public function findForShop(int $id, int $shopId): ?Inventory
    {
        return $this->query()
            ->where('id', $id)
            ->where('shop_id', $shopId)
            ->with(['category', 'supplier', 'movements'])
            ->first();
    }

    public function lowStockForShop(int $shopId): Collection
    {
        return $this->query()
            ->where('shop_id', $shopId)
            ->whereColumn('quantity', '<=', 'min_stock')
            ->where('status', 'active')
            ->with(['supplier'])
            ->orderBy('quantity')
            ->get();
    }

    public function createForShop(int $shopId, array $data): Inventory
    {
        return $this->create([...$data, 'shop_id' => $shopId]);
    }
}
