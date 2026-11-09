<?php

namespace App\Repositories;

use App\Models\Inventory;
use App\Models\ShopOrder;
use App\Models\ShopService;
use Illuminate\Support\Collection;

/** Persistence for the supplies a service uses, and for what an order took from stock. */
class ServiceSupplyUsageRepository
{
    public const SOURCE_SERVICE = 'service';

    /** @return Collection<int, Inventory> The service's supplies, each with pivot quantity and unit. */
    public function suppliesOfService(ShopService $service): Collection
    {
        return $service->supplies()->get();
    }

    /** What the service already took from stock for this order, as the stored pivot rows. */
    public function serviceRowsOfOrder(ShopOrder $order): Collection
    {
        return $order->serviceSupplies()->get();
    }

    public function lockInventory(int $shopId, int $inventoryId): ?Inventory
    {
        return Inventory::query()
            ->where('shop_id', $shopId)
            ->where('id', $inventoryId)
            ->lockForUpdate()
            ->first();
    }

    public function changeStock(Inventory $item, int $delta): void
    {
        $item->update(['quantity' => $item->quantity + $delta]);
    }

    public function attachServiceRow(ShopOrder $order, Inventory $item, int $quantity, string $unit, ?int $movementId): void
    {
        $order->serviceSupplies()->attach($item->id, [
            'quantity_used' => $quantity,
            'unit' => $unit,
            'source' => self::SOURCE_SERVICE,
            'unit_price' => null,
            'unit_cost' => $item->unit_price,
            'inventory_movement_id' => $movementId,
        ]);
    }

    public function detachServiceRows(ShopOrder $order): void
    {
        $order->serviceSupplies()->detach();
    }
}
