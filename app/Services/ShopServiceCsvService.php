<?php

namespace App\Services;

use App\Models\ShopService;
use App\Repositories\ShopServiceRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ShopServiceCsvService
{
    public const COLUMNS = [
        'service_name',
        'category',
        'description',
        'pricing_model',
        'price_per_kg',
        'bundle_price',
        'bundle_weight_kg',
        'status',
        'price_per_piece',
    ];

    /** Columns an older file may leave out. */
    private const OPTIONAL_COLUMNS = ['price_per_piece'];

    private const MAX_ROWS = 500;

    public function __construct(
        private readonly ShopServiceRepository $repository,
        private readonly ServiceCategoryService $categories,
    ) {}

    /** @return list<list<string|int|float|null>> Header row followed by one row per active service. */
    public function exportRows(int $shopId): array
    {
        $rows = $this->repository->allByShop($shopId)->map(fn (ShopService $service) => [
            $service->service_name,
            $service->category,
            $service->description,
            $service->pricing_model,
            $service->price_per_kg,
            $service->bundle_price,
            $service->bundle_weight_kg,
            $service->is_active ? 'Active' : 'Inactive',
            $service->price_per_piece,
        ])->all();

        return [self::COLUMNS, ...$rows];
    }

    /** @return list<list<string|int|float|null>> Header row plus one example row. */
    public function templateRows(): array
    {
        return [
            self::COLUMNS,
            ['Wash & Fold', 'full_service', 'Wash, dry and fold', 'per_kg', 60, '', '', 'Active', ''],
        ];
    }

    /**
     * Imports every row or none: one invalid row stops the whole import.
     *
     * @return array{imported: int, errors: list<string>}
     */
    public function import(int $shopId, UploadedFile $file): array
    {
        $parsed = $this->parse($file);

        if ($parsed['errors'] !== []) {
            return ['imported' => 0, 'errors' => $parsed['errors']];
        }

        $existing = $this->repository->allByShop($shopId)
            ->map(fn (ShopService $s) => mb_strtolower($s->service_name))
            ->all();
        $allowedCategories = $this->categories->allowedKeys($shopId);
        $errors = [];
        $valid = [];
        $seen = [];

        foreach ($parsed['rows'] as $line => $row) {
            $result = $this->validateRow($row, $allowedCategories);

            if ($result['errors'] !== []) {
                foreach ($result['errors'] as $message) {
                    $errors[] = "Row {$line}: {$message}";
                }

                continue;
            }

            $name = mb_strtolower($result['data']['service_name']);

            if (in_array($name, $existing, true) || isset($seen[$name])) {
                $errors[] = "Row {$line}: a service named \"{$result['data']['service_name']}\" already exists.";

                continue;
            }

            $seen[$name] = true;
            $valid[] = [...$result['data'], 'shop_id' => $shopId];
        }

        if ($errors !== []) {
            return ['imported' => 0, 'errors' => $errors];
        }

        DB::transaction(function () use ($valid) {
            foreach ($valid as $data) {
                $this->repository->create($data);
            }
        });

        return ['imported' => count($valid), 'errors' => []];
    }

    /** @return array{rows: array<int, array<string, string>>, errors: list<string>} Rows keyed by their CSV line number. */
    private function parse(UploadedFile $file): array
    {
        $handle = fopen($file->getRealPath(), 'r');

        if ($handle === false) {
            return ['rows' => [], 'errors' => ['The file could not be read.']];
        }

        $header = fgetcsv($handle);

        if (is_array($header) && isset($header[0])) {
            $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $header[0]);
        }

        $header = array_map(fn ($h) => strtolower(trim((string) $h)), $header ?: []);
        $missing = array_diff(self::COLUMNS, self::OPTIONAL_COLUMNS, $header);

        if ($missing !== []) {
            fclose($handle);

            return ['rows' => [], 'errors' => ['Missing column(s): '.implode(', ', $missing).'.']];
        }

        $rows = [];
        $line = 1;

        while (($values = fgetcsv($handle)) !== false) {
            $line++;

            if ($values === [null] || count(array_filter($values, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue;
            }

            if (count($rows) >= self::MAX_ROWS) {
                fclose($handle);

                return ['rows' => [], 'errors' => ['A CSV can contain at most '.self::MAX_ROWS.' services.']];
            }

            $rows[$line] = array_map('trim', array_combine($header, array_pad(array_slice($values, 0, count($header)), count($header), '')));
        }

        fclose($handle);

        return $rows === []
            ? ['rows' => [], 'errors' => ['The CSV has no service rows.']]
            : ['rows' => $rows, 'errors' => []];
    }

    /** @return array{data: array<string, mixed>, errors: list<string>} */
    private function validateRow(array $row, array $allowedCategories): array
    {
        $row = array_map(fn ($v) => $v === '' ? null : $v, $row);
        $row['pricing_model'] = isset($row['pricing_model']) ? strtolower($row['pricing_model']) : null;
        $row['category'] = isset($row['category']) ? strtolower($row['category']) : null;
        $status = strtolower((string) ($row['status'] ?? 'active'));

        $validator = Validator::make([...$row, 'status' => $status], [
            'service_name' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::in($allowedCategories)],
            'description' => ['nullable', 'string'],
            'pricing_model' => ['required', Rule::in(ShopService::PRICING_MODELS)],
            'price_per_kg' => [
                Rule::requiredIf(fn () => $row['pricing_model'] === 'per_kg'),
                'nullable', 'numeric', 'min:1', 'max:9999',
            ],
            'bundle_price' => [
                Rule::requiredIf(fn () => $row['pricing_model'] === 'per_bundle'),
                'nullable', 'numeric', 'min:1', 'max:99999',
            ],
            'bundle_weight_kg' => [
                Rule::requiredIf(fn () => $row['pricing_model'] === 'per_bundle'),
                'nullable', 'numeric', 'min:0.1', 'max:500',
            ],
            'price_per_piece' => [
                Rule::requiredIf(fn () => $row['pricing_model'] === 'per_piece'),
                'nullable', 'numeric', 'min:1', 'max:9999',
            ],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        if ($validator->fails()) {
            return ['data' => [], 'errors' => $validator->errors()->all()];
        }

        $model = $row['pricing_model'];

        return [
            'data' => [
                'service_name' => $row['service_name'],
                'category' => $row['category'],
                'description' => $row['description'],
                'pricing_model' => $row['pricing_model'],
                'price_per_kg' => $model === 'per_kg' ? $row['price_per_kg'] : null,
                'bundle_price' => $model === 'per_bundle' ? $row['bundle_price'] : null,
                'bundle_weight_kg' => $model === 'per_bundle' ? $row['bundle_weight_kg'] : null,
                'price_per_piece' => $model === 'per_piece' ? ($row['price_per_piece'] ?? null) : null,
                'is_active' => $status === 'active',
            ],
            'errors' => [],
        ];
    }
}
