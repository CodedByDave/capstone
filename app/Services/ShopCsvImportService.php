<?php

namespace App\Services;

use App\Repositories\ExpenseRepository;
use App\Repositories\InventoryCategoryRepository;
use App\Repositories\ShopRepository;
use App\Services\Csv\CsvReader;
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
    ];

    private const COLUMNS = [
        'inventory-categories' => ['name', 'description'],
        'promotions' => ['name', 'description', 'type', 'value', 'min_order_amount', 'status', 'starts_at', 'ends_at'],
        'expenses' => ['title', 'amount', 'expense_date', 'category', 'description', 'reference_number'],
    ];

    private const NOUNS = [
        'inventory-categories' => 'categories',
        'promotions' => 'promotions',
        'expenses' => 'expenses',
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
        $errors = [];
        $valid = [];
        $seen = [];

        foreach ($parsed['rows'] as $line => $row) {
            $result = $this->validateRow($dataset, $row);

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

    /** @return array{data: array<string, mixed>, errors: list<string>} */
    private function validateRow(string $dataset, array $row): array
    {
        $row = array_map(fn ($v) => $v === '' ? null : $v, $row);

        if ($dataset === 'promotions') {
            $row['type'] = isset($row['type']) ? strtolower($row['type']) : null;
            $row['status'] = strtolower((string) ($row['status'] ?? 'active'));
        }

        $validator = Validator::make($row, match ($dataset) {
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
        });

        if ($validator->fails()) {
            return ['data' => [], 'errors' => $validator->errors()->all()];
        }

        return ['data' => $this->shape($dataset, $row), 'errors' => []];
    }

    /** @return array<string, mixed> */
    private function shape(string $dataset, array $row): array
    {
        return match ($dataset) {
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
            };
        }
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
