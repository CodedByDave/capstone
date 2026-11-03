<?php

namespace App\Repositories;

use App\DTOs\TableSort;
use App\Models\Inventory;
use App\Models\InventoryCategory;
use App\Models\Supplier;
use App\Repositories\Concerns\AppliesTableSort;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class InventoryRepository extends Repository
{
    use AppliesTableSort;

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
            ->with(['category', 'supplier']);

        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%");
            });
        }

        if (! empty($filters['category_id'])) {
            $query->where('inventory_categories_id', $filters['category_id']);
        }

        if (! empty($filters['supplier_id'])) {
            $query->where('supplier_id', $filters['supplier_id']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['stock']) && $filters['stock'] === 'low') {
            $query->whereColumn('quantity', '<=', 'min_stock');
        }

        $this->applySorting($query, $filters['sort_by'] ?? 'name', $filters['sort_direction'] ?? 'asc');

        return $query->paginate($perPage);
    }

    /**
     * Sorts the list by one of its column headers. Category and supplier sort by their name, and
     * every sort falls back to the item name so equal rows keep a stable order across pages.
     */
    private function applySorting($query, string $sortBy, string $direction): void
    {
        $direction = $direction === 'desc' ? 'desc' : 'asc';

        match ($sortBy) {
            'category' => $query->orderBy(
                InventoryCategory::query()
                    ->select('name')
                    ->whereColumn('inventory_categories.id', 'inventory.inventory_categories_id'),
                $direction,
            ),
            'supplier' => $query->orderBy(
                Supplier::query()
                    ->select('name')
                    ->whereColumn('suppliers.id', 'inventory.supplier_id'),
                $direction,
            ),
            'quantity', 'unit_price', 'status' => $query->orderBy($sortBy, $direction),
            default => null,
        };

        $query->orderBy('name', $sortBy === 'name' ? $direction : 'asc')->orderBy('id');
    }

    public function allForShop(int $shopId): Collection
    {
        return $this->query()
            ->where('shop_id', $shopId)
            ->with(['category', 'supplier'])
            ->orderBy('name')
            ->get();
    }

    /** Active items the shop charges for as an add-on, for the customer-facing price list. */
    public function addonItemsForShop(int $shopId): Collection
    {
        return $this->query()
            ->where('shop_id', $shopId)
            ->where('status', 'active')
            ->where('addon_enabled', true)
            ->orderBy('name')
            ->get(['id', 'name', 'unit', 'addon_name', 'addon_quantity', 'addon_price']);
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

    /** Active items at or below their minimum that have never had a low stock alert. */
    public function lowStockWithoutAlerts(): Collection
    {
        return $this->query()
            ->whereColumn('quantity', '<=', 'min_stock')
            ->where('status', 'active')
            ->whereDoesntHave('lowStockAlerts')
            ->get();
    }

    public function archivedForShop(int $shopId, int $perPage = 15, ?TableSort $sort = null): LengthAwarePaginator
    {
        $query = $this->query()
            ->onlyTrashed()
            ->where('shop_id', $shopId)
            ->with(['category', 'supplier']);

        $sorted = $this->applyTableSort($query, $sort, [
            'name' => 'name',
            'quantity' => 'quantity',
            'deleted_at' => 'deleted_at',
            'category' => fn ($q, $dir) => $q->orderBy(
                InventoryCategory::query()
                    ->select('name')
                    ->whereColumn('inventory_categories.id', 'inventory.inventory_categories_id'),
                $dir,
            ),
            'supplier' => fn ($q, $dir) => $q->orderBy(
                Supplier::query()
                    ->select('name')
                    ->whereColumn('suppliers.id', 'inventory.supplier_id'),
                $dir,
            ),
        ]);

        if (! $sorted) {
            $query->orderByDesc('deleted_at');
        }

        return $query->paginate($perPage);
    }

    public function restoreForShop(int $id, int $shopId): bool
    {
        $item = $this->query()->onlyTrashed()->where('shop_id', $shopId)->findOrFail($id);

        return (bool) $item->restore();
    }

    public function createForShop(int $shopId, array $data): Inventory
    {
        return $this->create([...$data, 'shop_id' => $shopId]);
    }
}
