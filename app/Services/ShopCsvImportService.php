<?php

namespace App\Services;

use App\Repositories\EmployeeRepository;
use App\Repositories\ExpenseRepository;
use App\Repositories\InventoryCategoryRepository;
use App\Repositories\ShopCustomerRepository;
use App\Repositories\ShopRepository;
use App\Repositories\SupplierRepository;
use App\Repositories\UserRepository;
use App\Rules\PhilippineMobileNumber;
use App\Services\Csv\CsvReader;
use App\Support\CustomerIdentity;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

/**
 * Imports a shop's master data (inventory categories, promotions, expenses) from CSV.
 *
 * Records with side effects such as login accounts or stock movements (suppliers, inventory items)
 * and records the system produces itself (orders, payments, attendance, payroll) are not importable.
 */
class ShopCsvImportService
{
    /** Module permission each importable dataset needs for staff; owners may import all of them. */
    public const MODULES = [
        'inventory-categories' => 'Inventory Management',
        'promotions' => 'Operations',
        'expenses' => 'Finance Management',
        'suppliers' => 'Inventory Management',
        'inventory' => 'Inventory Management',
        'attendance' => 'Staff Management',
        'customers' => 'Operations',
    ];

    private const COLUMNS = [
        'customers' => ['name', 'phone', 'email'],
        'suppliers' => ['name', 'contact_person', 'email', 'phone', 'address', 'lead_time_days', 'status', 'notes'],
        'inventory' => ['name', 'category', 'supplier', 'unit', 'package_unit', 'package_size', 'quantity', 'min_stock', 'max_stock', 'unit_price', 'status'],
        'attendance' => ['employee_id', 'date', 'status', 'time_in', 'time_out', 'remarks'],
        'inventory-categories' => ['name', 'description'],
        'promotions' => ['name', 'description', 'type', 'value', 'min_order_amount', 'status', 'starts_at', 'ends_at'],
        'expenses' => ['title', 'amount', 'expense_date', 'category', 'description', 'reference_number'],
    ];

    private const NOUNS = [
        'inventory-categories' => 'categories',
        'promotions' => 'promotions',
        'expenses' => 'expenses',
        'suppliers' => 'suppliers',
        'inventory' => 'inventory items',
        'attendance' => 'attendance records',
        'customers' => 'customers',
    ];

    private const MAX_ROWS = 500;

    public function __construct(
        private readonly CsvReader $reader,
        private readonly ShopRepository $shops,
        private readonly InventoryCategoryRepository $categories,
        private readonly InventoryCategoryService $categoryService,
        private readonly PromotionService $promotions,
        private readonly ExpenseService $expenses,
        private readonly ExpenseRepository $expenseRepository,
        private readonly ActivityLogService $activity,
        private readonly SupplierService $supplierService,
        private readonly SupplierRepository $suppliers,
        private readonly InventoryService $inventoryService,
        private readonly AttendanceService $attendanceService,
        private readonly EmployeeRepository $employees,
        private readonly UserRepository $users,
        private readonly ShopCustomerService $customerService,
        private readonly ShopCustomerRepository $customers,
    ) {}

    /** @return list<string> */
    public static function datasets(): array
    {
        return array_keys(self::MODULES);
    }

    public function templateFilename(string $dataset): string
    {
        return $dataset.'_import_template.csv';
    }

    /** @return list<list<string|int|float|null>> Header row plus one example row. */
    public function templateRows(string $dataset): array
    {
        return [
            $this->columns($dataset),
            match ($dataset) {
                'inventory-categories' => ['Detergents', 'Washing powders and liquids'],
                'promotions' => ['Rainy day deal', 'Discount on wet-weather loads', 'percentage', 10, 300, 'Active', '2026-12-01', '2026-12-31'],
                'expenses' => ['Electricity bill', 4500, '2026-11-15', 'Utilities', 'November bill', 'OR-1001'],
                'suppliers' => ['Sunrise Supplies', 'Maria Cruz', 'maria@sunrise.example', '09171234567', 'Imus, Cavite', 7, 'Active', ''],
                'inventory' => ['Liquid detergent', 'Detergents', 'Sunrise Supplies', 'ml', 'bottle', 1000, 20, 5, 40, 120, 'Active'],
                'attendance' => ['EMP-001', '2026-11-15', 'present', '08:00', '17:00', ''],
                'customers' => ['Maria Santos', '09171234567', 'maria@example.com'],
                default => throw new InvalidArgumentException("Unknown import dataset [{$dataset}]."),
            },
        ];
    }

    /**
     * Imports every row or none: one invalid row stops the whole import.
     *
     * @return array{imported: int, errors: list<string>}
     */
    public function import(int $shopId, string $dataset, UploadedFile $file): array
    {
        $parsed = $this->reader->read($file, $this->columns($dataset), self::MAX_ROWS, self::NOUNS[$dataset]);

        if ($parsed['errors'] !== []) {
            return ['imported' => 0, 'errors' => $parsed['errors']];
        }

        $existing = $dataset === 'inventory-categories'
            ? $this->categories->allForShop($shopId)->map(fn ($c) => mb_strtolower($c->name))->all()
            : [];
        $lookups = $this->lookups($shopId, $dataset);
        $errors = [];
        $valid = [];
        $seen = [];

        foreach ($parsed['rows'] as $line => $row) {
            $result = $this->validateRow($dataset, $row, $lookups);

            foreach ($result['errors'] as $message) {
                $errors[] = "Row {$line}: {$message}";
            }

            if ($result['errors'] !== []) {
                continue;
            }

            if ($dataset === 'inventory-categories') {
                $key = mb_strtolower($result['data']['name']);

                if (in_array($key, $existing, true) || isset($seen[$key])) {
                    $errors[] = "Row {$line}: a category named \"{$result['data']['name']}\" already exists.";

                    continue;
                }

                $seen[$key] = true;
            }

            // Customers are told apart by name only, like orders do.
            if ($dataset === 'customers') {
                $identity = CustomerIdentity::key($result['data']['name']);

                if ($this->customers->findByIdentity($shopId, $identity) !== null) {
                    $errors[] = "Row {$line}: a customer named \"{$result['data']['name']}\" already exists.";

                    continue;
                }

                if (isset($seen['customer:'.$identity])) {
                    $errors[] = "Row {$line}: this row repeats an earlier row in the file.";

                    continue;
                }

                $seen['customer:'.$identity] = true;
            }

            $key = $this->uniqueKey($dataset, $result['data']);

            if ($key !== null) {
                if (isset($seen[$key])) {
                    $errors[] = "Row {$line}: this row repeats an earlier row in the file.";

                    continue;
                }

                $seen[$key] = true;
            }

            $valid[] = $result['data'];
        }

        if ($errors !== []) {
            return ['imported' => 0, 'errors' => $errors];
        }

        DB::transaction(function () use ($shopId, $dataset, $valid) {
            $this->persist($shopId, $dataset, $valid);
        });

        $this->logImport($shopId, $dataset, count($valid));

        return ['imported' => count($valid), 'errors' => []];
    }

    /** @return list<string> */
    private function columns(string $dataset): array
    {
        return self::COLUMNS[$dataset] ?? throw new InvalidArgumentException("Unknown import dataset [{$dataset}].");
    }

    /** @return array<string, array<string, int>> Names and codes the file's rows may point at. */
    private function lookups(int $shopId, string $dataset): array
    {
        return match ($dataset) {
            'inventory' => [
                'categories' => $this->categories->allForShop($shopId)
                    ->mapWithKeys(fn ($c) => [mb_strtolower($c->name) => $c->id])->all(),
                'suppliers' => $this->suppliers->allActiveForShop($shopId)
                    ->mapWithKeys(fn ($s) => [mb_strtolower($s->name) => $s->id])->all(),
            ],
            'attendance' => ['employees' => $this->employees->idsByEmployeeCode($shopId)],
            default => [],
        };
    }

    /** A value that must not repeat within one file, or null when rows may repeat. */
    private function uniqueKey(string $dataset, array $data): ?string
    {
        return match ($dataset) {
            'suppliers' => 'supplier:'.mb_strtolower($data['email']),
            'attendance' => 'attendance:'.$data['employee_id'].':'.$data['date'],
            default => null,
        };
    }

    /** @return array{data: array<string, mixed>, errors: list<string>} */
    private function validateRow(string $dataset, array $row, array $lookups = []): array
    {
        $row = array_map(fn ($v) => $v === '' ? null : $v, $row);

        if ($dataset === 'promotions') {
            $row['type'] = isset($row['type']) ? strtolower($row['type']) : null;
            $row['status'] = strtolower((string) ($row['status'] ?? 'active'));
        }

        if (in_array($dataset, ['suppliers', 'inventory', 'attendance'], true)) {
            $row['status'] = strtolower((string) ($row['status'] ?? ($dataset === 'attendance' ? '' : 'active')));
        }

        $validator = Validator::make($row, match ($dataset) {
            'customers' => [
                'name' => ['required', 'string', 'max:255'],
                'phone' => ['nullable', 'string', 'max:20', new PhilippineMobileNumber],
                'email' => ['nullable', 'email', 'max:255'],
            ],
            'inventory-categories' => [
                'name' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string', 'max:1000'],
            ],
            'promotions' => [
                'name' => ['required', 'string', 'max:100'],
                'description' => ['nullable', 'string', 'max:500'],
                'type' => ['required', Rule::in(['percentage', 'fixed'])],
                'value' => ['required', 'numeric', 'min:0.01'],
                'min_order_amount' => ['nullable', 'numeric', 'min:0'],
                'status' => ['required', Rule::in(['active', 'inactive'])],
                'starts_at' => ['nullable', 'date'],
                'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            ],
            'expenses' => [
                'title' => ['required', 'string', 'max:255'],
                'amount' => ['required', 'numeric', 'min:0.01'],
                'expense_date' => ['required', 'date'],
                'category' => ['nullable', 'string', 'max:100'],
                'description' => ['nullable', 'string', 'max:1000'],
                'reference_number' => ['nullable', 'string', 'max:100'],
            ],
            'suppliers' => [
                'name' => ['required', 'string', 'max:255'],
                'contact_person' => ['nullable', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', fn ($a, $v, $fail) => $this->users->emailTaken((string) $v)
                    ? $fail('Another account already uses this email address.') : null],
                'phone' => ['nullable', 'string', 'max:20', new PhilippineMobileNumber],
                'address' => ['nullable', 'string', 'max:1000'],
                'lead_time_days' => ['required', 'integer', 'min:0', 'max:365'],
                'status' => ['required', Rule::in(['active', 'inactive'])],
                'notes' => ['nullable', 'string', 'max:2000'],
            ],
            'inventory' => [
                'name' => ['required', 'string', 'max:255'],
                'category' => ['nullable', 'string', fn ($a, $v, $fail) => isset($lookups['categories'][mb_strtolower((string) $v)])
                    ? null : $fail("The category \"{$v}\" does not exist. Create it first.")],
                'supplier' => ['nullable', 'string', fn ($a, $v, $fail) => isset($lookups['suppliers'][mb_strtolower((string) $v)])
                    ? null : $fail("The supplier \"{$v}\" does not exist. Create it first.")],
                'unit' => ['required', 'string', 'max:50'],
                'package_unit' => ['required', 'string', 'max:50'],
                'package_size' => ['required', 'integer', 'min:1'],
                'quantity' => ['required', 'integer', 'min:0'],
                'min_stock' => ['required', 'integer', 'min:0'],
                'max_stock' => ['nullable', 'integer', 'min:0', 'gt:min_stock'],
                'unit_price' => ['nullable', 'numeric', 'min:0', 'max:9999999.99999999'],
                'status' => ['required', Rule::in(['active', 'inactive'])],
            ],
            'attendance' => [
                'employee_id' => ['required', fn ($a, $v, $fail) => isset($lookups['employees'][mb_strtolower((string) $v)])
                    ? null : $fail("No employee has the ID \"{$v}\".")],
                'date' => ['required', 'date_format:Y-m-d'],
                'status' => ['required', Rule::in(['present', 'absent', 'late', 'half_day'])],
                'time_in' => ['nullable', 'date_format:H:i'],
                'time_out' => ['nullable', 'date_format:H:i'],
                'remarks' => ['nullable', 'string', 'max:255'],
            ],
        });

        if ($validator->fails()) {
            return ['data' => [], 'errors' => $validator->errors()->all()];
        }

        return ['data' => $this->shape($dataset, $row, $lookups), 'errors' => []];
    }

    /** @return array<string, mixed> */
    private function shape(string $dataset, array $row, array $lookups = []): array
    {
        return match ($dataset) {
            'suppliers' => [
                'name' => $row['name'],
                'contact_person' => $row['contact_person'],
                'email' => $row['email'],
                'phone' => $row['phone'],
                'address' => $row['address'],
                'lead_time_days' => $row['lead_time_days'],
                'status' => $row['status'],
                'notes' => $row['notes'],
            ],
            'inventory' => [
                'name' => $row['name'],
                'inventory_categories_id' => isset($row['category']) ? $lookups['categories'][mb_strtolower($row['category'])] : null,
                'supplier_id' => isset($row['supplier']) ? $lookups['suppliers'][mb_strtolower($row['supplier'])] : null,
                'unit' => $row['unit'],
                'package_unit' => $row['package_unit'],
                'package_size' => $row['package_unit'] === $row['unit'] ? 1 : $row['package_size'],
                'quantity' => $row['quantity'],
                'min_stock' => $row['min_stock'],
                'max_stock' => $row['max_stock'],
                'unit_price' => $row['unit_price'],
                'status' => $row['status'],
            ],
            'attendance' => [
                'employee_id' => $lookups['employees'][mb_strtolower($row['employee_id'])],
                'date' => $row['date'],
                'status' => $row['status'],
                'time_in' => $row['time_in'],
                'time_out' => $row['time_out'],
                'remarks' => $row['remarks'],
            ],
            'customers' => ['name' => $row['name'], 'phone' => $row['phone'], 'email' => $row['email']],
            'inventory-categories' => ['name' => $row['name'], 'description' => $row['description']],
            'promotions' => [
                'name' => $row['name'],
                'description' => $row['description'],
                'type' => $row['type'],
                'value' => $row['value'],
                'min_order_amount' => $row['min_order_amount'],
                'is_active' => $row['status'] === 'active',
                'starts_at' => $row['starts_at'],
                'ends_at' => $row['ends_at'],
            ],
            'expenses' => [
                'title' => $row['title'],
                'amount' => $row['amount'],
                'expense_date' => $row['expense_date'],
                'category' => $row['category'],
                'description' => $row['description'],
                'reference_number' => $row['reference_number'],
            ],
        };
    }

    /** @param  list<array<string, mixed>>  $records */
    private function persist(int $shopId, string $dataset, array $records): void
    {
        $shop = $dataset === 'inventory-categories' ? null : $this->shops->findShopById($shopId);

        foreach ($records as $data) {
            match ($dataset) {
                'inventory-categories' => $this->categoryService->create($shopId, $data),
                'promotions' => $this->promotions->create($shop, $data),
                'expenses' => $this->createExpense($shop, $data),
                'suppliers' => $this->supplierService->create($shopId, $data),
                'inventory' => $this->inventoryService->create($shopId, $data),
                'attendance' => $this->attendanceService->markAttendance($shop, $data),
                'customers' => $this->customerService->resolveForOrder($shopId, $data['name'], $data['phone'], $data['email']),
            };
        }
    }

    /** Records one expense (and its category) the same way the Expenses screen does. */
    public function createExpenseRecord(int $shopId, array $data): void
    {
        $this->createExpense($this->shops->findShopById($shopId), $data);
    }

    private function createExpense($shop, array $data): void
    {
        $categoryName = $data['category'];
        unset($data['category']);

        $data['category_id'] = $categoryName === null
            ? null
            : $this->expenseRepository->firstOrCreateCategory($shop, $categoryName)->id;

        $this->expenses->create($shop, $data);
    }

    private function logImport(int $shopId, string $dataset, int $count): void
    {
        $shop = $this->shops->findShopById($shopId);

        if ($shop !== null) {
            $this->activity->log($shop, 'imported', ['dataset' => $dataset, 'rows' => $count], $shopId, 'Data import');
        }
    }
}
