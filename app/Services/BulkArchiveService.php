<?php

namespace App\Services;

use App\Models\Delivery;
use App\Models\Expense;
use App\Models\Inventory;
use App\Models\InventoryCategory;
use App\Models\Payroll;
use App\Models\Promotion;
use App\Models\Shop;
use App\Models\ShopOrder;
use App\Models\ShopService;
use App\Models\Supplier;
use App\Models\User;
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
        'order' => ['model' => ShopOrder::class, 'key' => 'payment_public_id'],
        'delivery' => ['model' => Delivery::class, 'key' => 'public_id'],
        'payroll' => ['model' => Payroll::class, 'key' => 'public_id'],
    ];

    public function __construct(
        private readonly BulkRecordRepository $records,
        private readonly InventoryService $inventory,
        private readonly InventoryCategoryService $categories,
        private readonly SupplierService $suppliers,
        private readonly ExpenseService $expenses,
        private readonly ShopServiceService $services,
        private readonly PromotionService $promotions,
        private readonly OrderArchiveService $orders,
        private readonly LogisticsService $logistics,
        private readonly PayrollService $payroll,
    ) {}

    /**
     * @param  list<string>  $ids
     * @return int How many rows were archived.
     */
    public function archive(string $resource, array $ids, Shop $shop, User $actor): int
    {
        return $this->run($resource, $ids, $shop, archived: false, action: function (Model $record) use ($resource, $actor): void {
            match ($resource) {
                'inventory' => $this->inventory->delete($record),
                'inventory-category' => $this->categories->delete($record),
                'supplier' => $this->suppliers->delete($record),
                'expense' => $this->expenses->delete($record),
                'service' => $this->services->delete($record),
                'promotion' => $this->promotions->delete($record),
                'order' => $this->orders->archive($record, $actor->id),
                'delivery' => $this->logistics->deleteDelivery($actor, $record),
                'payroll' => $this->payroll->delete($record),
            };
        }, only: fn (Model $record): bool => $resource !== 'payroll' || $record->status !== 'finalized');
    }

    /**
     * @param  list<string>  $ids
     * @return int How many rows were restored.
     */
    public function restore(string $resource, array $ids, Shop $shop, User $actor): int
    {
        return $this->run($resource, $ids, $shop, archived: true, action: function (Model $record) use ($resource, $shop, $actor): void {
            match ($resource) {
                'inventory' => $this->inventory->restore($record->getKey(), $shop->id),
                'inventory-category' => $this->categories->restore($record->getKey()),
                'supplier' => $this->suppliers->restore($record->public_id, $shop->id),
                'expense' => $this->expenses->restore($record->public_id, $shop),
                'service' => $this->services->restore($record),
                'promotion' => $this->promotions->restore($record),
                'order' => $this->orders->restore($record->payment_public_id, $shop->id, $actor->id),
                'delivery' => $this->logistics->restoreDelivery($actor, $record->public_id),
                'payroll' => $this->payroll->restore($record->public_id, $shop),
            };
        });
    }

    /** @param list<string> $ids */
    private function run(string $resource, array $ids, Shop $shop, bool $archived, callable $action, ?callable $only = null): int
    {
        $config = self::RESOURCES[$resource] ?? throw new InvalidArgumentException("Unknown bulk resource [{$resource}].");

        // All or nothing: one failing row must not leave the selection half archived.
        return DB::transaction(function () use ($config, $ids, $shop, $archived, $action, $only): int {
            $records = $this->records->forShop($config['model'], $ids, $shop->id, $archived, $config['key']);

            // Rows that may not be archived (a finalized payroll) are left out, not failed.
            if ($only !== null) {
                $records = $records->filter($only)->values();
            }

            $records->each($action);

            return $records->count();
        });
    }
}
