<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Inventory;
use App\Models\InventoryCategory;
use App\Models\Promotion;
use App\Models\Shop;
use App\Models\ShopService;
use App\Models\Supplier;
use App\Repositories\BulkRecordRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Archives or restores many table rows in one go. Each row goes through the same single-row
 * service method the table's row action uses, so logging and side effects stay identical.
 */
class BulkArchiveService
{
    /** The resources that can be archived in bulk, and the column that identifies their table rows. */
    private const RESOURCES = [
        'inventory' => ['model' => Inventory::class, 'key' => 'id'],
        'inventory-category' => ['model' => InventoryCategory::class, 'key' => 'id'],
        'supplier' => ['model' => Supplier::class, 'key' => 'public_id'],
        'expense' => ['model' => Expense::class, 'key' => 'public_id'],
        'service' => ['model' => ShopService::class, 'key' => 'id'],
        'promotion' => ['model' => Promotion::class, 'key' => 'id'],
    ];

    public function __construct(
        private readonly BulkRecordRepository $records,
        private readonly InventoryService $inventory,
        private readonly InventoryCategoryService $categories,
        private readonly SupplierService $suppliers,
        private readonly ExpenseService $expenses,
        private readonly ShopServiceService $services,
        private readonly PromotionService $promotions,
    ) {}

    /**
     * @param  list<string>  $ids
     * @return int How many rows were archived.
     */
    public function archive(string $resource, array $ids, Shop $shop): int
    {
        return $this->run($resource, $ids, $shop, archived: false, action: function (Model $record) use ($resource): void {
            match ($resource) {
                'inventory' => $this->inventory->delete($record),
                'inventory-category' => $this->categories->delete($record),
                'supplier' => $this->suppliers->delete($record),
                'expense' => $this->expenses->delete($record),
                'service' => $this->services->delete($record),
                'promotion' => $this->promotions->delete($record),
            };
        });
    }

    /**
     * @param  list<string>  $ids
     * @return int How many rows were restored.
     */
    public function restore(string $resource, array $ids, Shop $shop): int
    {
        return $this->run($resource, $ids, $shop, archived: true, action: function (Model $record) use ($resource, $shop): void {
            match ($resource) {
                'inventory' => $this->inventory->restore($record->getKey(), $shop->id),
                'inventory-category' => $this->categories->restore($record->getKey()),
                'supplier' => $this->suppliers->restore($record->public_id, $shop->id),
                'expense' => $this->expenses->restore($record->public_id, $shop),
                'service' => $this->services->restore($record),
                'promotion' => $this->promotions->restore($record),
            };
        });
    }

    /** @param list<string> $ids */
    private function run(string $resource, array $ids, Shop $shop, bool $archived, callable $action): int
    {
        $config = self::RESOURCES[$resource] ?? throw new InvalidArgumentException("Unknown bulk resource [{$resource}].");

        // All or nothing: one failing row must not leave the selection half archived.
        return DB::transaction(function () use ($config, $ids, $shop, $archived, $action): int {
            $records = $this->records->forShop($config['model'], $ids, $shop->id, $archived, $config['key']);
            $records->each($action);

            return $records->count();
        });
    }
}
