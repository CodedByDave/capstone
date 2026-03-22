<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\Shop;
use App\Repositories\InventoryRepository;
use App\Repositories\LowStockAlertRepository;
use App\Repositories\InventoryMovementRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    public function __construct(
        private readonly InventoryRepository       $inventoryRepository,
        private readonly InventoryMovementRepository $movementRepository,
        private readonly LowStockAlertRepository   $alertRepository,
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

            // Trigger low stock alert if initial quantity is already at/below threshold
            if ($inventory->quantity <= $inventory->min_stock) {
                $this->alertRepository->createIfNotExists(
                    inventoryId: $inventory->id,
                    shopId: $shopId,
                    quantityAtAlert: $inventory->quantity,
                    minStockAtAlert: $inventory->min_stock,
                );
            }

            return $inventory;
        });
    }

    public function update(Inventory $inventory, array $data): Inventory
    {
        return DB::transaction(function () use ($inventory, $data) {
            $this->inventoryRepository->update($inventory, $data);

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
        string    $type,
        int       $quantity,
        ?string   $referenceNumber = null,
        ?string   $notes = null,
    ): Inventory {
        return DB::transaction(function () use ($inventory, $type, $quantity, $referenceNumber, $notes) {
            $newQuantity = $inventory->quantity + $quantity;

            // Prevent stock going negative
            if ($newQuantity < 0) {
                throw new \InvalidArgumentException(
                    "Insufficient stock. Available: {$inventory->quantity}, requested: " . abs($quantity)
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

            // Trigger low stock alert if threshold crossed
            if ($inventory->quantity <= $inventory->min_stock) {
                $this->alertRepository->createIfNotExists(
                    inventoryId: $inventory->id,
                    shopId: $inventory->shop_id,
                    quantityAtAlert: $inventory->quantity,
                    minStockAtAlert: $inventory->min_stock,
                );
            }

            return $inventory;
        });
    }

    public function lowStock(int $shopId): Collection
    {
        return $this->inventoryRepository->lowStockForShop($shopId);
    }
}
