<?php

namespace App\Services;

use App\Models\Inventory;
use App\Repositories\InventoryMovementRepository;
use App\Repositories\InventoryRepository;
use App\Repositories\LowStockAlertRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    public function __construct(
        private readonly InventoryRepository $inventoryRepository,
        private readonly InventoryMovementRepository $movementRepository,
        private readonly LowStockAlertRepository $alertRepository,
        private readonly ExpenseService $expenseService,
    ) {}

    public function paginate(int $shopId, int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->inventoryRepository->paginateForShop($shopId, $perPage, $filters);
    }

    public function all(int $shopId): Collection
    {
        return $this->inventoryRepository->allForShop($shopId);
    }

    public function find(int $id, int $shopId): ?Inventory
    {
        return $this->inventoryRepository->findForShop($id, $shopId);
    }

    public function create(int $shopId, array $data): Inventory
    {
        return DB::transaction(function () use ($shopId, $data) {
            $inventory = $this->inventoryRepository->createForShop($shopId, $data);

            // Log initial stock as a restock movement if quantity > 0
            if ($inventory->quantity > 0) {
                $this->movementRepository->createMovement(
                    inventory: $inventory,
                    type: 'restock',
                    quantity: $inventory->quantity,
                    userId: auth()->id(),
                    referenceNumber: null,
                    notes: 'Initial stock on creation',
                );
            }

            $this->alertIfLowStock($inventory);

            return $inventory;
        });
    }

    public function update(Inventory $inventory, array $data): Inventory
    {
        return DB::transaction(function () use ($inventory, $data) {
            $this->inventoryRepository->update($inventory, $data);
            $this->alertIfLowStock($inventory->refresh());

            return $inventory->fresh(['category', 'supplier']);
        });
    }

    public function delete(Inventory $inventory): bool
    {
        return DB::transaction(function () use ($inventory) {
            return $this->inventoryRepository->delete($inventory);
        });
    }

    /**
     * Adjust stock (restock, usage, adjustment, return, damage).
     * Logs a movement and triggers a low stock alert if needed.
     */
    public function adjustStock(
        Inventory $inventory,
        string $type,
        int $quantity,
        ?string $referenceNumber = null,
        ?string $notes = null,
    ): Inventory {
        return DB::transaction(function () use ($inventory, $type, $quantity, $referenceNumber, $notes) {
            $newQuantity = $inventory->quantity + $quantity;

            // Prevent stock going negative
            if ($newQuantity < 0) {
                throw new \InvalidArgumentException(
                    "Insufficient stock. Available: {$inventory->quantity}, requested: ".abs($quantity)
                );
            }

            // Log the movement first (uses current quantity for before/after snapshot)
            $this->movementRepository->createMovement(
                inventory: $inventory,
                type: $type,
                quantity: $quantity,
                userId: auth()->id(),
                referenceNumber: $referenceNumber,
                notes: $notes,
            );

            // Update the stock
            $this->inventoryRepository->update($inventory, ['quantity' => $newQuantity]);
            $inventory->refresh();

            // A restock is money spent, so it belongs in Finance expenses.
            if ($type === 'restock') {
                $this->expenseService->recordInventoryRestock($inventory, $quantity, $referenceNumber);
            }

            $this->alertIfLowStock($inventory);

            return $inventory;
        });
    }

    /**
     * Raises an unread low stock alert when the item sits at or below its minimum. Every path
     * that changes stock or the minimum calls this, so the alerts page never misses an item.
     */
    public function alertIfLowStock(Inventory $inventory): void
    {
        if ($inventory->quantity > $inventory->min_stock) {
            return;
        }

        $this->alertRepository->createIfNotExists(
            inventoryId: $inventory->id,
            shopId: $inventory->shop_id,
            quantityAtAlert: $inventory->quantity,
            minStockAtAlert: $inventory->min_stock,
        );
    }

    /**
     * One-time catch-up for items that fell to their minimum before alerts were raised on every
     * stock change. Items that already have an alert, even a dismissed one, are left alone.
     *
     * @return int how many alerts were created
     */
    public function backfillLowStockAlerts(): int
    {
        $items = $this->inventoryRepository->lowStockWithoutAlerts();

        DB::transaction(fn () => $items->each(fn (Inventory $item) => $this->alertIfLowStock($item)));

        return $items->count();
    }

    public function lowStock(int $shopId): Collection
    {
        return $this->inventoryRepository->lowStockForShop($shopId);
    }
}
