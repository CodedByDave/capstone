<?php

namespace App\Services;

use App\Models\ShopOrder;
use App\Models\User;
use App\Repositories\InventoryRepository;
use App\Repositories\PurchaseOrderRepository;
use App\Repositories\ShopOrderRepository;
use App\Repositories\ShopRepository;
use App\Repositories\ShopRoleRepository;
use App\Services\Csv\CsvReader;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

/**
 * Imports records that move money, stock or access: payments, transactions, stock movements,
 * role permissions and purchase orders.
 *
 * Every row goes through the same service the screens use, so order balances, stock levels and
 * finance totals stay consistent. A row that would break a rule (overpaying an order, taking stock
 * below zero, an unknown order or item) rejects the whole file, and nothing is imported.
 */
class ShopLedgerCsvImportService
{
    /** Module permission each dataset needs for staff; permissions are owner-only (see ShopDataImportController). */
    public const MODULES = [
        'income' => 'Finance Management',
        'transactions' => 'Finance Management',
        'stock-movements' => 'Inventory Management',
        'purchase-orders' => 'Inventory Management',
        'permissions' => 'HRM',
    ];

    /** Datasets only the shop owner may import. */
    public const OWNER_ONLY = ['permissions'];

    private const COLUMNS = [
        'income' => ['order_number', 'amount', 'method', 'reference', 'note'],
        'transactions' => ['type', 'order_number', 'title', 'category', 'amount', 'method', 'reference', 'description', 'date'],
        'stock-movements' => ['item', 'type', 'quantity', 'reference_number', 'notes'],
        'purchase-orders' => ['reference', 'supplier', 'item', 'quantity', 'notes'],
        'permissions' => ['role', 'module', 'action'],
    ];

    private const NOUNS = [
        'income' => 'payments',
        'transactions' => 'transactions',
        'stock-movements' => 'stock movements',
        'purchase-orders' => 'purchase order lines',
        'permissions' => 'permissions',
    ];

    private const ACTIONS = ['view', 'create', 'update', 'archive', 'export', 'import', 'manage', 'roles', 'users'];

    private const MAX_ROWS = 500;

    public function __construct(
        private readonly CsvReader $reader,
        private readonly ShopCsvImportService $records,
        private readonly OrderPaymentService $payments,
        private readonly ShopOrderRepository $orders,
        private readonly InventoryService $inventoryService,
        private readonly InventoryRepository $inventory,
        private readonly PurchaseOrderService $purchaseOrders,
        private readonly PurchaseOrderRepository $purchaseOrderRepository,
        private readonly ShopRoleRepository $roles,
        private readonly ActivityLogService $activity,
        private readonly ShopRepository $shops,
    ) {}

    /** @return list<string> */
    public static function datasets(): array
    {
        return array_keys(self::MODULES);
    }

    /** @return list<list<string|int|float|null>> */
    public function templateRows(string $dataset): array
    {
        return [
            $this->columns($dataset),
            match ($dataset) {
                'income' => ['ORD-1001', 250, 'cash', 'OR-55', 'Partial payment'],
                'transactions' => ['income', 'ORD-1001', '', '', 250, 'gcash', 'GC-77', '', '2026-11-15'],
                'stock-movements' => ['Liquid detergent', 'restock', 10, 'DR-100', 'Delivery'],
                'purchase-orders' => ['PO-A', 'Sunrise Supplies', 'Detergent 5L', 3, 'Weekly order'],
                'permissions' => ['clerk', 'Operations', 'view'],
            },
        ];
    }

    /** @return array{imported: int, errors: list<string>} */
    public function import(User $actor, int $shopId, string $dataset, UploadedFile $file): array
    {
        $parsed = $this->reader->read($file, $this->columns($dataset), self::MAX_ROWS, self::NOUNS[$dataset]);

        if ($parsed['errors'] !== []) {
            return ['imported' => 0, 'errors' => $parsed['errors']];
        }

        $lookups = $this->lookups($shopId, $dataset);
        $errors = [];
        $valid = [];

        foreach ($parsed['rows'] as $line => $row) {
            $result = $this->validateRow($dataset, $row, $lookups);

            foreach ($result['errors'] as $message) {
                $errors[] = "Row {$line}: {$message}";
            }

            if ($result['errors'] === []) {
                $valid[] = ['line' => $line, ...$result['data']];
            }
        }

        if ($dataset === 'purchase-orders' && $errors === []) {
            $errors = $this->checkPurchaseOrderGroups($valid);
        }

        if ($errors !== []) {
            return ['imported' => 0, 'errors' => $errors];
        }

        try {
            $count = DB::transaction(fn () => $this->persist($actor, $shopId, $dataset, $valid));
        } catch (RowRejected $rejected) {
            return ['imported' => 0, 'errors' => [$rejected->getMessage()]];
        }

        $shop = $this->shops->findShopById($shopId);

        if ($shop !== null) {
            $this->activity->log($shop, 'imported', ['dataset' => $dataset, 'rows' => $count], $shopId, 'Data import');
        }

        return ['imported' => $count, 'errors' => []];
    }

    /** @return list<string> */
    private function columns(string $dataset): array
    {
        return self::COLUMNS[$dataset] ?? throw new InvalidArgumentException("Unknown import dataset [{$dataset}].");
    }

    /** @return array<string, mixed> */
    private function lookups(int $shopId, string $dataset): array
    {
        return match ($dataset) {
            'income', 'transactions' => ['orders' => $this->orders->idsByOrderNumber($shopId)],
            'stock-movements' => ['items' => $this->inventory->idsByName($shopId)],
            'purchase-orders' => ['suppliers' => $this->purchaseOrderRepository->catalogForShop($shopId)
                ->mapWithKeys(fn ($s) => [mb_strtolower($s->name) => $s])->all()],
            'permissions' => [
                'roles' => $this->roles->roleNames($shopId),
                'modules' => array_map('mb_strtolower', [...$this->roles->moduleNames(), 'Logistics']),
            ],
        };
    }

    /** @return array{data: array<string, mixed>, errors: list<string>} */
    private function validateRow(string $dataset, array $row, array $lookups): array
    {
        $row = array_map(fn ($v) => $v === '' ? null : $v, $row);
        $orderExists = fn ($a, $v, $fail) => isset($lookups['orders'][mb_strtolower((string) $v)])
            ? null : $fail("No order has the number \"{$v}\".");

        if ($dataset === 'transactions') {
            return $this->validateTransaction($row, $orderExists, $lookups);
        }

        $validator = Validator::make($row, match ($dataset) {
            'income' => [
                'order_number' => ['required', $orderExists],
                'amount' => ['required', 'numeric', 'gt:0'],
                'method' => ['required', Rule::in(ShopOrder::PAYMENT_METHODS)],
                'reference' => ['nullable', 'string', 'max:100'],
                'note' => ['nullable', 'string', 'max:255'],
            ],
            'stock-movements' => [
                'item' => ['required', fn ($a, $v, $fail) => isset($lookups['items'][mb_strtolower((string) $v)])
                    ? null : $fail("No inventory item is named \"{$v}\".")],
                'type' => ['required', Rule::in(['restock', 'usage', 'adjustment', 'return', 'damage'])],
                'quantity' => ['required', 'integer', 'not_in:0'],
                'reference_number' => ['nullable', 'string', 'max:100'],
                'notes' => ['nullable', 'string', 'max:1000'],
            ],
            'purchase-orders' => [
                'reference' => ['required', 'string', 'max:50'],
                'supplier' => ['required', fn ($a, $v, $fail) => isset($lookups['suppliers'][mb_strtolower((string) $v)])
                    ? null : $fail("No active supplier is named \"{$v}\".")],
                'item' => ['required', 'string', 'max:255'],
                'quantity' => ['required', 'numeric', 'gt:0', 'max:100000', 'decimal:0,2'],
                'notes' => ['nullable', 'string', 'max:1000'],
            ],
            'permissions' => [
                'role' => ['required', fn ($a, $v, $fail) => in_array(mb_strtolower((string) $v), $lookups['roles'], true)
                    ? null : $fail("The role \"{$v}\" does not exist.")],
                'module' => ['required', fn ($a, $v, $fail) => in_array(mb_strtolower((string) $v), $lookups['modules'], true)
                    ? null : $fail("The module \"{$v}\" does not exist.")],
                'action' => ['required', Rule::in(self::ACTIONS)],
            ],
        });

        if ($validator->fails()) {
            return ['data' => [], 'errors' => $validator->errors()->all()];
        }

        $extra = [];

        if ($dataset === 'purchase-orders') {
            $supplier = $lookups['suppliers'][mb_strtolower($row['supplier'])];
            $item = $supplier->items->first(fn ($i) => mb_strtolower($i->name) === mb_strtolower($row['item']));

            if ($item === null) {
                return ['data' => [], 'errors' => ["{$supplier->name} does not sell \"{$row['item']}\"."]];
            }

            $extra = ['supplier_public_id' => $supplier->public_id, 'item_public_id' => $item->public_id];
        }

        return ['data' => [...$row, ...$extra, 'order_id' => $lookups['orders'][mb_strtolower((string) ($row['order_number'] ?? ''))] ?? null,
            'inventory_id' => $lookups['items'][mb_strtolower((string) ($row['item'] ?? ''))] ?? null, '_dataset' => $dataset], 'errors' => []];
    }

    /** @return array{data: array<string, mixed>, errors: list<string>} */
    private function validateTransaction(array $row, callable $orderExists, array $lookups): array
    {
        $type = strtolower((string) ($row['type'] ?? ''));

        if (! in_array($type, ['income', 'expense'], true)) {
            return ['data' => [], 'errors' => ['The type must be "income" or "expense".']];
        }

        $validator = Validator::make($row, $type === 'income'
            ? [
                'order_number' => ['required', $orderExists],
                'amount' => ['required', 'numeric', 'gt:0'],
                'method' => ['required', Rule::in(ShopOrder::PAYMENT_METHODS)],
                'reference' => ['nullable', 'string', 'max:100'],
            ]
            : [
                'title' => ['required', 'string', 'max:255'],
                'amount' => ['required', 'numeric', 'min:0.01'],
                'date' => ['required', 'date'],
                'category' => ['nullable', 'string', 'max:100'],
                'description' => ['nullable', 'string', 'max:1000'],
                'reference' => ['nullable', 'string', 'max:100'],
            ]);

        if ($validator->fails()) {
            return ['data' => [], 'errors' => $validator->errors()->all()];
        }

        return ['data' => [
            ...$row,
            'type' => $type,
            'order_id' => $lookups['orders'][mb_strtolower((string) ($row['order_number'] ?? ''))] ?? null,
            '_dataset' => 'transactions',
        ], 'errors' => []];
    }

    /** @param  list<array<string, mixed>>  $rows */
    private function checkPurchaseOrderGroups(array $rows): array
    {
        $errors = [];
        $groups = [];

        foreach ($rows as $row) {
            $key = mb_strtolower($row['reference']);
            $groups[$key]['supplier'] ??= $row['supplier_public_id'];
            $groups[$key]['items'][] = $row['item_public_id'];

            if ($groups[$key]['supplier'] !== $row['supplier_public_id']) {
                $errors[] = "Row {$row['line']}: order \"{$row['reference']}\" mixes suppliers.";
            }

            if (count($groups[$key]['items']) !== count(array_unique($groups[$key]['items']))) {
                $errors[] = "Row {$row['line']}: order \"{$row['reference']}\" lists \"{$row['item']}\" twice.";
            }
        }

        return $errors;
    }

    /** @param  list<array<string, mixed>>  $rows */
    private function persist(User $actor, int $shopId, string $dataset, array $rows): int
    {
        if ($dataset === 'purchase-orders') {
            return $this->persistPurchaseOrders($actor, $rows);
        }

        foreach ($rows as $row) {
            try {
                match ($dataset) {
                    'income' => $this->recordPayment($actor, $row),
                    'transactions' => $this->recordTransaction($actor, $shopId, $row),
                    'stock-movements' => $this->inventoryService->adjustStock(
                        $this->inventory->findForShop($row['inventory_id'], $shopId),
                        $row['type'],
                        (int) $row['quantity'],
                        $row['reference_number'],
                        $row['notes'],
                    ),
                    'permissions' => $this->roles->grant($shopId, mb_strtolower($row['role']), [[
                        'module' => $this->moduleName($row['module']),
                        'action' => $row['action'],
                    ]]),
                };
            } catch (ValidationException $e) {
                throw new RowRejected("Row {$row['line']}: ".collect($e->errors())->flatten()->first());
            } catch (InvalidArgumentException $e) {
                throw new RowRejected("Row {$row['line']}: ".$e->getMessage());
            }
        }

        return count($rows);
    }

    private function recordPayment(User $actor, array $row): void
    {
        $this->payments->recordShopPayment($actor, $this->orders->findById($row['order_id']), [
            'amount' => $row['amount'],
            'method' => $row['method'],
            'reference' => $row['reference'] ?? null,
            'note' => $row['note'] ?? null,
        ]);
    }

    private function recordTransaction(User $actor, int $shopId, array $row): void
    {
        if ($row['type'] === 'income') {
            $this->recordPayment($actor, $row);

            return;
        }

        $this->records->createExpenseRecord($shopId, [
            'title' => $row['title'],
            'amount' => $row['amount'],
            'expense_date' => $row['date'],
            'category' => $row['category'],
            'description' => $row['description'],
            'reference_number' => $row['reference'],
        ]);
    }

    /** The module's canonical spelling, so "operations" and "Operations" grant the same permission. */
    private function moduleName(string $given): string
    {
        foreach ([...$this->roles->moduleNames(), 'Logistics'] as $name) {
            if (mb_strtolower($name) === mb_strtolower($given)) {
                return $name;
            }
        }

        return $given;
    }

    /** @param  list<array<string, mixed>>  $rows */
    private function persistPurchaseOrders(User $actor, array $rows): int
    {
        $groups = [];

        foreach ($rows as $row) {
            $key = mb_strtolower($row['reference']);
            $groups[$key]['supplier'] = $row['supplier_public_id'];
            $groups[$key]['notes'] = $groups[$key]['notes'] ?? ($row['notes'] ?? null);
            $groups[$key]['items'][] = ['supplier_item' => $row['item_public_id'], 'quantity' => $row['quantity']];
            $groups[$key]['line'] = $groups[$key]['line'] ?? $row['line'];
        }

        foreach ($groups as $group) {
            try {
                $this->purchaseOrders->create($actor, [
                    'supplier' => $group['supplier'],
                    'notes' => $group['notes'],
                    'send' => false,
                    'items' => $group['items'],
                ]);
            } catch (ValidationException $e) {
                throw new RowRejected("Row {$group['line']}: ".collect($e->errors())->flatten()->first());
            }
        }

        return count($groups);
    }
}
