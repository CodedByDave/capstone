<?php

namespace App\Services;

use App\Models\Shop;
use App\Repositories\ShopRepository;
use App\Rules\PhilippineMobileNumber;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Bulk-updates existing shops from a CSV, in the same layout the shop export produces. Rows are matched
 * to a shop by owner email and shop name; a CSV never creates a shop. Blank cells keep the current value.
 * The whole file is checked first and applied in one transaction, so a bad row changes nothing.
 */
class ShopImportService
{
    /** Columns that are written to the shop when the CSV supplies a value. */
    private const UPDATABLE = [
        'phone', 'block_street', 'municipality', 'barangay', 'postal_code', 'status',
        'bir_expiry_date', 'dti_expiry_date', 'mayors_expiry_date', 'sanitary_expiry_date',
    ];

    public function __construct(
        private readonly ShopRepository $shops,
        private readonly ActivityLogService $activityLog,
    ) {}

    /**
     * @return array{updated: int, unchanged: int}
     *
     * @throws ValidationException when the file is unreadable, lacks the key columns, or has a bad row
     */
    public function import(UploadedFile $file): array
    {
        $rows = $this->readRows($file);
        $plan = [];

        foreach ($rows as $number => $row) {
            $shop = $this->shops->findByOwnerEmailAndName($row['owner_email'], $row['shop_name']);

            if ($shop === null) {
                $this->fail("Row {$number}: no shop named \"{$row['shop_name']}\" belongs to {$row['owner_email']}. Imports only update existing shops.");
            }

            $plan[] = [$shop, $this->validatedChanges($number, $row)];
        }

        $result = DB::transaction(function () use ($plan): array {
            $updated = 0;
            $unchanged = 0;

            foreach ($plan as [$shop, $attributes]) {
                $saved = $this->shops->applyImportedDetails($shop, $attributes);
                $changes = collect($saved->getChanges())->except('updated_at')->keys()->all();

                if ($changes === []) {
                    $unchanged++;

                    continue;
                }

                $updated++;
                $this->activityLog->log(
                    $saved,
                    'imported_update',
                    ['fields' => $changes],
                    module: 'Shop Management',
                );
            }

            return ['updated' => $updated, 'unchanged' => $unchanged];
        });

        return $result;
    }

    /** @return array<int, array<string, string>> rows keyed by their line number in the file */
    private function readRows(UploadedFile $file): array
    {
        $handle = fopen($file->getRealPath(), 'r');

        if ($handle === false) {
            $this->fail('The CSV file could not be opened.');
        }

        try {
            $header = fgetcsv($handle, 0, ',', '"', '');

            if ($header === false) {
                $this->fail('The CSV file is empty.');
            }

            $header = array_map(
                fn ($column) => Str::snake(trim((string) preg_replace('/^\xEF\xBB\xBF/', '', (string) $column))),
                $header,
            );

            $missing = array_diff(['shop_name', 'owner_email'], $header);
            if ($missing !== []) {
                $this->fail('Missing required columns: '.implode(', ', $missing).'.');
            }

            $rows = [];
            $number = 1;

            while (($values = fgetcsv($handle, 0, ',', '"', '')) !== false) {
                $number++;

                if (count(array_filter($values, fn ($value) => trim((string) $value) !== '')) === 0) {
                    continue;
                }

                $values = array_pad($values, count($header), '');
                $row = array_map(fn ($value) => trim((string) $value), array_combine($header, array_slice($values, 0, count($header))));

                if ($row['shop_name'] === '' || ! filter_var($row['owner_email'], FILTER_VALIDATE_EMAIL)) {
                    $this->fail("Row {$number}: a shop name and a valid owner email are required.");
                }

                $rows[$number] = $row;
            }
        } finally {
            fclose($handle);
        }

        if ($rows === []) {
            $this->fail('The CSV file has no shop rows.');
        }

        return $rows;
    }

    /**
     * The row's non-blank updatable values, checked with the same rules as the Edit Shop form.
     *
     * @param  array<string, string>  $row
     * @return array<string, string>
     */
    private function validatedChanges(int $number, array $row): array
    {
        $values = collect(self::UPDATABLE)
            ->mapWithKeys(fn ($column) => [$column => $row[$column] ?? ''])
            ->filter(fn ($value) => $value !== '');

        if ($values->has('status')) {
            $values['status'] = mb_strtolower($values['status']);
        }

        $validator = Validator::make($values->all(), [
            'phone' => ['string', 'max:20', new PhilippineMobileNumber],
            'block_street' => ['string', 'max:255'],
            'municipality' => ['string', 'max:255'],
            'barangay' => ['string', 'max:255'],
            'postal_code' => ['string', 'max:255'],
            'status' => ['in:active,pending,disabled'],
            'bir_expiry_date' => ['date'],
            'dti_expiry_date' => ['date'],
            'mayors_expiry_date' => ['date'],
            'sanitary_expiry_date' => ['date'],
        ]);

        if ($validator->fails()) {
            $this->fail("Row {$number}: ".$validator->errors()->first());
        }

        return $values->all();
    }

    private function fail(string $message): never
    {
        throw ValidationException::withMessages(['file' => $message]);
    }
}
