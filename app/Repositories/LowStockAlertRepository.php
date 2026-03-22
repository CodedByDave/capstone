<?php

namespace App\Repositories;

use App\Models\LowStockAlert;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class LowStockAlertRepository extends Repository
{
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

    public function paginateForShop(int $shopId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->where('shop_id', $shopId)
            ->with(['inventory'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
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
                'status'       => 'unread',
            ],
            [
                'shop_id'            => $shopId,
                'quantity_at_alert'  => $quantityAtAlert,
                'min_stock_at_alert' => $minStockAtAlert,
            ]
        );
    }
}
