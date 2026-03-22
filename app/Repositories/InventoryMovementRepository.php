<?php

namespace App\Repositories;

use App\Models\Inventory;
use App\Models\InventoryMovement;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class InventoryMovementRepository extends Repository
{
    public function __construct(InventoryMovement $inventoryMovement)
    {
        parent::__construct($inventoryMovement);
    }

    public function paginateForInventory(
        int $inventoryId,
        int $perPage = 15
    ): LengthAwarePaginator {
        return $this->query()
            ->where('inventory_id', $inventoryId)
            ->with(['user'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function createMovement(
        Inventory $inventory,
        string $type,
        int $quantity,
        ?int $userId = null,
        ?string $referenceNumber = null,
        ?string $notes = null
    ): InventoryMovement {
        $before = $inventory->quantity;
        $after  = $before + $quantity;

        return $this->create([
            'inventory_id'     => $inventory->id,
            'shop_id'          => $inventory->shop_id,
            'user_id'          => $userId,
            'type'             => $type,
            'quantity'         => $quantity,
            'quantity_before'  => $before,
            'quantity_after'   => $after,
            'reference_number' => $referenceNumber,
            'notes'            => $notes,
        ]);
    }

    public function recentForShop(int $shopId, int $limit = 10): Collection
    {
        return $this->query()
            ->where('shop_id', $shopId)
            ->with(['inventory', 'user'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }
}
