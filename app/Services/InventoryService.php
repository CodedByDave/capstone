<?php

namespace App\Services;

use App\DTOs\TableSort;
use App\Models\Inventory;
use App\Repositories\InventoryMovementRepository;
use App\Repositories\InventoryRepository;
use App\Repositories\LowStockAlertRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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

    public function create(int $shopId, array $data, ?UploadedFile $photo = null): Inventory
    {
        $data = $this->withSalesOptions($data);
        $photoPath = $photo ? $this->storePhoto($photo, $shopId) : null;

        try {
            return $this->createRecord($shopId, $data, $photoPath);
        } catch (\Throwable $exception) {
            if ($photoPath !== null) {
                Storage::disk('public')->delete($photoPath);
            }

            throw $exception;
        }
    }

    private function createRecord(int $shopId, array $data, ?string $photoPath): Inventory
    {
        if ($photoPath !== null) {
            $data['photo_path'] = $photoPath;
        }

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

                // Stock added now is money spent, so Finance expenses show it like any restock.
                $this->expenseService->recordInventoryRestock($inventory, $inventory->quantity);
            }

            $this->alertIfLowStock($inventory);

            return $inventory;
        });
    }

    public function update(Inventory $inventory, array $data, ?UploadedFile $photo = null, bool $removePhoto = false): Inventory
    {
        $data = $this->withSalesOptions($data);
        $oldPath = $inventory->photo_path;
        $newPath = null;

        if ($photo !== null) {
            $newPath = $this->storePhoto($photo, $inventory->shop_id);
            $data['photo_path'] = $newPath;
        } elseif ($removePhoto) {
            $data['photo_path'] = null;
        }

        try {
            $updated = DB::transaction(function () use ($inventory, $data) {
                $this->inventoryRepository->update($inventory, $data);
                $this->alertIfLowStock($inventory->refresh());

                return $inventory->fresh(['category', 'supplier']);
            });
        } catch (\Throwable $exception) {
            if ($newPath !== null) {
                Storage::disk('public')->delete($newPath);
            }

            throw $exception;
        }

        if ($oldPath && array_key_exists('photo_path', $data)) {
            Storage::disk('public')->delete($oldPath);
        }

        return $updated;
    }

    /**
     * Settings of a switched-off sales option are cleared, and `selling_price` (the per-unit price the
     * order flow charges for a supply) is derived from what is left: the add-on when it is on (₱15 for
     * 30 ml = ₱0.50/ml, which is how extra amounts are charged on an order), otherwise the retail
     * product (₱150 for a 1,000 ml bottle = ₱0.15/ml). With neither, the item carries no price.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function withSalesOptions(array $data): array
    {
        $addon = (bool) ($data['addon_enabled'] ?? false);
        $retail = (bool) ($data['retail_enabled'] ?? false);

        $data['addon_enabled'] = $addon;
        $data['retail_enabled'] = $retail;

        if (! $addon) {
            $data['addon_name'] = $data['addon_quantity'] = $data['addon_price'] = null;
        }
        if (! $retail) {
            $data['retail_name'] = $data['retail_pack_size'] = $data['retail_price'] = null;
        }

        $data['selling_price'] = match (true) {
            $addon => round((float) $data['addon_price'] / (int) $data['addon_quantity'], 4),
            $retail => round((float) $data['retail_price'] / (int) $data['retail_pack_size'], 4),
            default => null,
        };

        return $data;
    }

    private function storePhoto(UploadedFile $photo, int $shopId): string
    {
        return $photo->store("inventory-photos/{$shopId}", 'public');
    }

    public function delete(Inventory $inventory): bool
    {
        return DB::transaction(function () use ($inventory) {
            return $this->inventoryRepository->delete($inventory);
        });
    }

    public function archived(int $shopId, ?TableSort $sort = null, int $perPage = 15): LengthAwarePaginator
    {
        return $this->inventoryRepository->archivedForShop($shopId, $perPage, $sort);
    }

    public function restore(int $id, int $shopId): bool
    {
        return DB::transaction(fn () => $this->inventoryRepository->restoreForShop($id, $shopId));
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
        bool $recordExpense = true,
    ): Inventory {
        return DB::transaction(function () use ($inventory, $type, $quantity, $referenceNumber, $notes, $recordExpense) {
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

            // A restock is money spent, so it belongs in Finance expenses. A received purchase order
            // records one expense for the whole order at the supplier's price instead.
            if ($type === 'restock' && $recordExpense) {
                $this->expenseService->recordInventoryRestock($inventory, $quantity, $referenceNumber);
            }

            $this->alertIfLowStock($inventory);

            return $inventory;
        });
    }

    /** Takes stock out for an order's additional charges; the caller owns the transaction. */
    public function deductStock(Inventory $inventory, float $quantity): void
    {
        $this->inventoryRepository->decrementQuantity($inventory, $quantity);
        $this->alertIfLowStock($inventory->refresh());
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
