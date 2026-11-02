<?php

namespace App\Repositories;

use App\DTOs\TableSort;
use App\Models\Inventory;
use App\Models\LowStockAlert;
use App\Repositories\Concerns\AppliesTableSort;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class LowStockAlertRepository extends Repository
{
    use AppliesTableSort;

    public function __construct(LowStockAlert $lowStockAlert)
    {
        parent::__construct($lowStockAlert);
    }

    public function unreadForShop(int $shopId): Collection
    {
        return $this->query()
            ->where('shop_id', $shopId)
            ->where('status', 'unread')
            ->with(['inventory'])
            ->orderByDesc('created_at')
            ->get();
    }

    public function paginateForShop(int $shopId, int $perPage = 15, ?TableSort $sort = null): LengthAwarePaginator
    {
        $query = $this->query()
            ->where('shop_id', $shopId)
            ->with(['inventory']);

        $sorted = $this->applyTableSort($query, $sort, [
            'quantity_at_alert' => 'quantity_at_alert',
            'status' => 'status',
            'created_at' => 'created_at',
            'item' => fn ($q, $dir) => $q->orderBy(
                Inventory::withTrashed()
                    ->select('name')
                    ->whereColumn('inventory.id', 'low_stock_alerts.inventory_id'),
                $dir,
            ),
            'current' => fn ($q, $dir) => $q->orderBy(
                Inventory::withTrashed()
                    ->select('quantity')
                    ->whereColumn('inventory.id', 'low_stock_alerts.inventory_id'),
                $dir,
            ),
        ]);

        if (! $sorted) {
            $query->orderByDesc('created_at');
        }

        return $query->paginate($perPage);
    }

    public function unreadCountForShop(int $shopId): int
    {
        return $this->query()
            ->where('shop_id', $shopId)
            ->where('status', 'unread')
            ->count();
    }

    public function createIfNotExists(
        int $inventoryId,
        int $shopId,
        int $quantityAtAlert,
        int $minStockAtAlert
    ): LowStockAlert {
        return $this->updateOrCreate(
            [
                'inventory_id' => $inventoryId,
                'status' => 'unread',
            ],
            [
                'shop_id' => $shopId,
                'quantity_at_alert' => $quantityAtAlert,
                'min_stock_at_alert' => $minStockAtAlert,
            ]
        );
    }
}
