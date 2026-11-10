<?php

namespace App\Services;

use App\DTOs\TableSort;
use App\Models\Supplier;
use App\Models\SupplierItem;
use App\Models\User;
use App\Repositories\SupplierItemRepository;
use App\Repositories\SupplierRepository;
use Illuminate\Support\Facades\DB;

/** What a supplier does from their own dashboard: keep their stocked supplies and prices up to date. */
class SupplierPortalService
{
    public function __construct(
        private readonly SupplierRepository $suppliers,
        private readonly SupplierItemRepository $items,
        private readonly SupplierPriceListService $priceLists,
    ) {}

    /** The supplier record behind a login, or null once the shop archived or deactivated it. */
    public function activeSupplierFor(User $user): ?Supplier
    {
        return $this->suppliers->findActiveForUser($user->id);
    }

    public function dashboardData(Supplier $supplier, array $filters, TableSort $sort): array
    {
        $filters = [
            'search' => trim((string) ($filters['search'] ?? '')),
            'stock' => (string) ($filters['stock'] ?? ''),
            'per_page' => (int) ($filters['per_page'] ?? 10),
        ];
        $recent = $this->priceLists->recentForSupplier($supplier);

        return [
            'supplier' => [
                'name' => $supplier->name,
                'shop_name' => $supplier->shop?->shop_name,
            ],
            'items' => $this->items->paginateForSupplier(
                $supplier->id,
                $filters['search'],
                $filters['stock'],
                $sort,
                $filters['per_page'],
            ),
            'counts' => $this->items->stockCountsForSupplier($supplier->id),
            'priceLists' => $recent,
            'latestPriceList' => $recent[0] ?? null,
            'filters' => $filters,
        ];
    }

    public function createItem(Supplier $supplier, array $data): SupplierItem
    {
        return DB::transaction(fn () => $this->items->createForSupplier($supplier, $data));
    }

    public function updateItem(Supplier $supplier, string $publicId, array $data): SupplierItem
    {
        return DB::transaction(function () use ($supplier, $publicId, $data): SupplierItem {
            $item = $this->items->findForSupplierOrFail($publicId, $supplier->id);
            $this->items->update($item, $data);

            return $item->refresh();
        });
    }

    public function deleteItem(Supplier $supplier, string $publicId): void
    {
        DB::transaction(function () use ($supplier, $publicId): void {
            $this->items->delete($this->items->findForSupplierOrFail($publicId, $supplier->id));
        });
    }
}
