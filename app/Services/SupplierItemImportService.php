<?php

namespace App\Services;

use App\Exceptions\SupplierPortalException;
use App\Models\Supplier;
use App\Repositories\SupplierItemRepository;
use App\Services\Ocr\OcrSpaceClient;
use App\Services\Ocr\PriceListParser;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Adds many supplies at once, from a CSV file or a photo of a printed price list.
 *
 * Both sources are read into rows the supplier reviews and corrects first; nothing is saved
 * until they confirm. A row whose name the supplier already lists updates that supply instead
 * of adding a second one.
 */
class SupplierItemImportService
{
    public const MAX_ROWS = 500;

    /** CSV headings accepted for each field, so a sheet exported from elsewhere still reads. */
    private const COLUMNS = [
        'name' => ['name', 'supply', 'product', 'item', 'description', 'product name', 'item name', 'supply name'],
        'unit' => ['unit', 'uom', 'unit of measure', 'size'],
        'stock_quantity' => ['stock quantity', 'stock', 'in stock', 'quantity', 'qty'],
        'unit_price' => ['unit price', 'price', 'selling price', 'srp', 'amount'],
    ];

    public function __construct(
        private readonly SupplierItemRepository $items,
        private readonly OcrSpaceClient $ocr,
        private readonly PriceListParser $parser,
    ) {}

    /** @return list<list<string>> */
    public function templateRows(): array
    {
        return [
            ['name', 'unit', 'stock_quantity', 'unit_price'],
            ['Liquid Detergent', 'gallon', '24', '450.00'],
            ['Fabric Softener', '1 L bottle', '40', '199.00'],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     *
     * @throws SupplierPortalException when the file has no usable heading or rows
     */
    public function previewCsv(Supplier $supplier, UploadedFile $file): array
    {
        $handle = fopen($file->getRealPath(), 'r');

        try {
            $header = fgetcsv($handle);

            if ($header === false || $header === [null]) {
                throw new SupplierPortalException('The CSV file is empty.');
            }

            $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $header[0]);
            $positions = $this->columnPositions($header);

            if (! isset($positions['name'], $positions['unit_price'])) {
                throw new SupplierPortalException('The CSV needs a "name" and a "unit_price" column. Download the template to see the layout.');
            }

            $rows = [];
            while (($values = fgetcsv($handle)) !== false) {
                if (count(array_filter($values, fn ($value) => trim((string) $value) !== '')) === 0) {
                    continue;
                }
                if (count($rows) >= self::MAX_ROWS) {
                    throw new SupplierPortalException('A CSV can add up to '.self::MAX_ROWS.' supplies at a time. Split the file and import each part.');
                }

                $cell = fn (string $field) => isset($positions[$field]) ? trim((string) ($values[$positions[$field]] ?? '')) : '';
                $rows[] = [
                    'name' => mb_substr($cell('name'), 0, 255),
                    'unit' => mb_substr($cell('unit'), 0, 50),
                    'stock_quantity' => $this->number($cell('stock_quantity')),
                    'unit_price' => $this->number($cell('unit_price')),
                ];
            }
        } finally {
            fclose($handle);
        }

        if ($rows === []) {
            throw new SupplierPortalException('The CSV has a heading but no supplies under it.');
        }

        return $this->withExisting($supplier, $rows);
    }

    /**
     * @return list<array<string, mixed>>
     *
     * @throws SupplierPortalException when OCR is off, cannot read the photo, or finds no products
     */
    public function previewPhoto(Supplier $supplier, UploadedFile $photo): array
    {
        if (! $this->ocr->isConfigured()) {
            throw new SupplierPortalException('Photo scanning is not available right now. Import a CSV file or add the supplies one by one.');
        }

        $text = $this->ocr->extractText($photo);

        if (blank($text)) {
            throw new SupplierPortalException('The photo could not be read. Upload a sharper photo taken straight on, with the whole list in frame.');
        }

        $rows = array_map(fn (array $row) => [
            'name' => $row['name'],
            'unit' => mb_substr((string) ($row['unit'] ?? ''), 0, 50),
            'stock_quantity' => null,
            'unit_price' => $row['unit_price'],
        ], array_slice($this->parser->parse($text), 0, self::MAX_ROWS));

        if ($rows === []) {
            throw new SupplierPortalException('No products with prices were found on the photo. Make sure each product name and its price are visible.');
        }

        return $this->withExisting($supplier, $rows);
    }

    /**
     * Saves the reviewed rows: new names are added, names already listed are updated.
     *
     * @param  list<array{name: string, unit: ?string, stock_quantity: float|int|string, unit_price: float|int|string}>  $rows
     * @return array{added: int, updated: int}
     */
    public function import(Supplier $supplier, array $rows): array
    {
        return DB::transaction(function () use ($supplier, $rows): array {
            $existing = $this->items->byLowerNameForSupplier($supplier->id, array_column($rows, 'name'));
            $added = 0;
            $updated = 0;

            foreach ($this->lastRowPerName($rows) as $key => $row) {
                $data = [
                    'name' => trim($row['name']),
                    'unit' => filled($row['unit'] ?? null) ? trim($row['unit']) : null,
                    'stock_quantity' => $row['stock_quantity'],
                    'unit_price' => $row['unit_price'],
                ];

                if (isset($existing[$key])) {
                    $this->items->update($existing[$key], $data);
                    $updated++;
                } else {
                    $this->items->createForSupplier($supplier, $data);
                    $added++;
                }
            }

            return ['added' => $added, 'updated' => $updated];
        });
    }

    /**
     * Marks the rows that match a supply already listed, and fills a missing stock or unit from
     * it so a scanned price list does not wipe the stock count.
     */
    private function withExisting(Supplier $supplier, array $rows): array
    {
        $existing = $this->items->byLowerNameForSupplier($supplier->id, array_column($rows, 'name'));

        return array_map(function (array $row) use ($existing) {
            $match = $existing[mb_strtolower(trim($row['name']))] ?? null;

            return [
                ...$row,
                'stock_quantity' => $row['stock_quantity'] ?? ($match ? (float) $match->stock_quantity : 0),
                'unit' => $row['unit'] !== '' ? $row['unit'] : (string) ($match?->unit ?? ''),
                'existing' => $match !== null,
                'current_price' => $match ? (float) $match->unit_price : null,
            ];
        }, $rows);
    }

    /** The same name twice in one import keeps the later row. */
    private function lastRowPerName(array $rows): array
    {
        $byName = [];
        foreach ($rows as $row) {
            $byName[mb_strtolower(trim($row['name']))] = $row;
        }

        return $byName;
    }

    /** @return array<string, int> */
    private function columnPositions(array $header): array
    {
        $positions = [];
        foreach ($header as $index => $heading) {
            $heading = trim(preg_replace('/\s+/', ' ', str_replace(['_', '(₱)', '(php)'], [' ', '', ''], mb_strtolower((string) $heading))));
            foreach (self::COLUMNS as $field => $aliases) {
                if (! isset($positions[$field]) && in_array($heading, $aliases, true)) {
                    $positions[$field] = $index;
                }
            }
        }

        return $positions;
    }

    /** "₱1,250.00" becomes 1250.0; text that is not a number stays as typed so the review flags it. */
    private function number(string $value): float|string|null
    {
        if ($value === '') {
            return null;
        }
        $clean = str_ireplace([',', '₱', 'php', ' '], '', $value);

        return is_numeric($clean) ? round((float) $clean, 2) : $value;
    }
}
