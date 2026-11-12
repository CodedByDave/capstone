<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\ShopOrder;
use App\Repositories\InventoryMovementRepository;
use App\Repositories\ServiceSupplyUsageRepository;
use App\Repositories\ShopOrderRepository;
use Illuminate\Validation\ValidationException;

/**
 * Moves the stock of an order's hand-added supplies (add-ons, retail products, supplies the shop
 * picked) back to the shelf or out of it again, logging each change as an inventory movement.
 * The order keeps its list of supplies either way. Call inside the caller's transaction.
 */
class OrderSupplyStockService
{
    public function __construct(
        private readonly ShopOrderRepository $orders,
        private readonly ServiceSupplyUsageRepository $stock,
        private readonly InventoryMovementRepository $movements,
        private readonly InventoryService $inventory,
    ) {}

    /** Puts every hand-added supply of the order back in stock. */
    public function returnToStock(ShopOrder $order, ?int $userId, string $notes): void
    {
        foreach ($this->orders->manualSupplies($order) as $supply) {
            $this->move($order, $supply, +$this->quantityOf($supply), $userId, $notes);
        }
    }

    /**
     * Takes every hand-added supply of the order out of stock again.
     *
     * @throws ValidationException when an item no longer has enough stock
     */
    public function takeFromStock(ShopOrder $order, ?int $userId, string $notes, string $failurePrefix): void
    {
        foreach ($this->orders->manualSupplies($order) as $supply) {
            $this->move($order, $supply, -$this->quantityOf($supply), $userId, $notes, $failurePrefix);
        }
    }

    /** Stock is kept in whole units. */
    private function quantityOf(Inventory $supply): int
    {
        return (int) round((float) $supply->pivot->quantity_used);
    }

    private function move(ShopOrder $order, Inventory $supply, int $delta, ?int $userId, string $notes, string $failurePrefix = ''): void
    {
        $item = $this->stock->lockInventory($order->shop_id, $supply->id);

        // The supply was removed from inventory since, so there is no stock to move.
        if ($item === null || $delta === 0) {
            return;
        }

        if ($delta < 0 && $item->quantity < -$delta) {
            throw ValidationException::withMessages([
                'supplies' => "{$failurePrefix}not enough stock of {$item->name}. Available: {$item->quantity} {$item->unit}, needed: ".(-$delta).'.',
            ]);
        }

        $this->movements->createMovement(
            inventory: $item,
            type: $delta > 0 ? 'return' : 'usage',
            quantity: $delta,
            userId: $userId,
            referenceNumber: $order->order_number,
            notes: $notes,
        );

        $this->stock->changeStock($item, $delta);

        if ($delta < 0) {
            $this->inventory->alertIfLowStock($item->refresh());
        }
    }
}
