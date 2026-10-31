<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\PayrollItem;
use App\Models\ShopOrder;
use App\Models\ShopOrderPayment;
use App\Models\Supplier;
use App\Repositories\ShopExportRepository;
use App\Repositories\ShopRepository;
use Illuminate\Support\LazyCollection;
use RuntimeException;
use ZipArchive;

/** CSV copies of a shop's own data, one dataset at a time or all of them in a zip. */
class ShopDataExportService
{
    /** Module permission each dataset needs for staff; owners may export all of them. */
    public const MODULES = [
        'orders' => 'Operations',
        'services' => 'Operations',
        'income' => 'Finance Management',
        'expenses' => 'Finance Management',
        'inventory' => 'Inventory Management',
        'stock-movements' => 'Inventory Management',
        'suppliers' => 'Inventory Management',
        'employees' => 'HRM',
        'attendance' => 'HRM',
        'payroll' => 'HRM',
    ];

    public const LABELS = [
        'orders' => 'Orders',
        'services' => 'Services & pricing',
        'income' => 'Income (payments received)',
        'expenses' => 'Expenses',
        'inventory' => 'Inventory items',
        'stock-movements' => 'Stock movements',
        'suppliers' => 'Suppliers',
        'employees' => 'Employees',
        'attendance' => 'Attendance',
        'payroll' => 'Payroll',
    ];

    public function __construct(
        private readonly ShopExportRepository $repository,
        private readonly ShopServiceCsvService $services,
        private readonly ShopRepository $shops,
        private readonly ActivityLogService $activity,
    ) {}

    /** @return list<string> */
    public static function datasets(): array
    {
        return array_keys(self::MODULES);
    }

    public function filename(string $dataset): string
    {
        return $dataset.'_'.now()->format('Y-m-d').'.csv';
    }

    /**
     * Writes one dataset as UTF-8 CSV (with a BOM for Excel) to an open stream.
     *
     * @param  resource  $handle
     */
    public function writeCsv(int $shopId, string $dataset, $handle): void
    {
        [$headers, $rows] = $this->definition($shopId, $dataset);

        fwrite($handle, "\xEF\xBB\xBF");
        fputcsv($handle, $headers);

        foreach ($rows as $row) {
            fputcsv($handle, array_map($this->safeCell(...), $row));
        }
    }

    /** Records that a dataset was downloaded. */
    public function logExport(int $shopId, string $dataset): void
    {
        $shop = $this->shops->findShopById($shopId);

        if ($shop !== null) {
            $this->activity->log($shop, 'exported', ['dataset' => $dataset], $shopId, 'Data export');
        }
    }

    /**
     * Builds a zip of every dataset the caller may export and returns its temporary path.
     *
     * @param  list<string>|null  $datasets  Defaults to all datasets.
     */
    public function archive(int $shopId, ?array $datasets = null): string
    {
        $datasets ??= self::datasets();
        $path = tempnam(sys_get_temp_dir(), 'shop-export-');
        $zip = new ZipArchive;

        if ($zip->open($path, ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Could not create the export archive.');
        }

        $buffers = [];

        foreach ($datasets as $dataset) {
            $buffers[$dataset] = fopen('php://temp/maxmemory:'.(8 * 1024 * 1024), 'w+');
            $this->writeCsv($shopId, $dataset, $buffers[$dataset]);
            rewind($buffers[$dataset]);
            $zip->addFromString($this->filename($dataset), stream_get_contents($buffers[$dataset]));
            fclose($buffers[$dataset]);
        }

        $zip->close();

        return $path;
    }

    /** @return array{0: list<string>, 1: iterable<list<mixed>>} */
    private function definition(int $shopId, string $dataset): array
    {
        return match ($dataset) {
            'orders' => [
                ['Order number', 'Customer', 'Phone', 'Service', 'Pricing', 'Weight (kg)', 'Pieces', 'Bundles', 'Total', 'Amount paid', 'Payment status', 'Payment method', 'Status', 'Placed', 'Completed', 'Paid', 'Archived'],
                $this->map($this->repository->orders($shopId), fn (ShopOrder $o) => [
                    $o->order_number, $o->customer_name, $o->customer_phone, $o->service?->service_name, $o->pricing_model,
                    $o->actual_weight_kg ?? $o->estimated_weight_kg, $o->piece_count, $o->bundle_quantity,
                    $o->total_amount, $o->amount_paid, $o->payment_status, $o->payment_method, $o->status,
                    $o->created_at?->toDateTimeString(), $o->completed_at?->toDateTimeString(), $o->paid_at?->toDateTimeString(),
                    $o->trashed() ? 'Yes' : 'No',
                ]),
            ],
            'services' => $this->serviceRows($shopId),
            'income' => [
                ['Paid at', 'Order number', 'Customer', 'Amount', 'Method', 'Reference', 'Source'],
                $this->map($this->repository->income($shopId), fn (ShopOrderPayment $p) => [
                    $p->paid_at?->toDateTimeString(), $p->order?->order_number, $p->order?->customer_name,
                    $p->amount, $p->method, $p->reference, $p->source,
                ]),
            ],
            'expenses' => [
                ['Date', 'Title', 'Category', 'Amount', 'Source', 'Reference', 'Description', 'Archived'],
                $this->map($this->repository->expenses($shopId), fn (Expense $e) => [
                    $e->expense_date?->toDateString(), $e->title, $e->category?->name, $e->amount, $e->source,
                    $e->reference_number, $e->description, $e->trashed() ? 'Yes' : 'No',
                ]),
            ],
            'inventory' => [
                ['Name', 'Category', 'Supplier', 'Unit', 'Quantity', 'Minimum stock', 'Maximum stock', 'Unit price', 'Selling price', 'Status'],
                $this->map($this->repository->inventory($shopId), fn (Inventory $i) => [
                    $i->name, $i->category?->name, $i->supplier?->name, $i->unit, $i->quantity,
                    $i->min_stock, $i->max_stock, $i->unit_price, $i->selling_price, $i->status,
                ]),
            ],
            'stock-movements' => [
                ['Date', 'Item', 'Type', 'Quantity', 'Before', 'After', 'Reference', 'Notes'],
                $this->map($this->repository->stockMovements($shopId), fn (InventoryMovement $m) => [
                    $m->created_at?->toDateTimeString(), $m->inventory?->name, $m->type,
                    $m->quantity, $m->quantity_before, $m->quantity_after, $m->reference_number, $m->notes,
                ]),
            ],
            'suppliers' => [
                ['Name', 'Contact person', 'Email', 'Phone', 'Address', 'Lead time (days)', 'Status', 'Notes', 'Archived'],
                $this->map($this->repository->suppliers($shopId), fn (Supplier $s) => [
                    $s->name, $s->contact_person, $s->email, $s->phone, $s->address, $s->lead_time_days,
                    $s->status, $s->notes, $s->trashed() ? 'Yes' : 'No',
                ]),
            ],
            'employees' => [
                ['Employee ID', 'First name', 'Last name', 'Position', 'Employment type', 'Email', 'Phone', 'Hire date', 'Pay rate', 'Pay basis', 'Status', 'Archived'],
                $this->map($this->repository->employees($shopId), fn (Employee $e) => [
                    $e->employee_id, $e->first_name, $e->last_name, $e->position, $e->employment_type, $e->email, $e->phone,
                    $e->hire_date?->toDateString(), $e->pay_rate, $e->pay_basis, $e->status, $e->trashed() ? 'Yes' : 'No',
                ]),
            ],
            'attendance' => [
                ['Date', 'Employee ID', 'Employee', 'Status', 'Time in', 'Time out', 'Remarks'],
                $this->map($this->repository->attendance($shopId), fn (Attendance $a) => [
                    $a->date?->toDateString(), $a->employee?->employee_id, trim(($a->employee?->first_name ?? '').' '.($a->employee?->last_name ?? '')),
                    $a->status, $a->time_in, $a->time_out, $a->remarks,
                ]),
            ],
            'payroll' => [
                ['Payroll', 'Period start', 'Period end', 'Payroll status', 'Employee ID', 'Employee', 'Basic salary', 'Days worked', 'Days absent', 'Days late', 'Deductions', 'SSS', 'PhilHealth', 'Pag-IBIG', 'Withholding tax', 'Bonuses', 'Net pay'],
                $this->map($this->repository->payroll($shopId), fn (PayrollItem $i) => [
                    $i->payroll?->period_label, $i->payroll?->period_start?->toDateString(), $i->payroll?->period_end?->toDateString(), $i->payroll?->status,
                    $i->employee?->employee_id, trim(($i->employee?->first_name ?? '').' '.($i->employee?->last_name ?? '')),
                    $i->basic_salary, $i->days_worked, $i->days_absent, $i->days_late, $i->deductions,
                    $i->sss_contribution, $i->philhealth_contribution, $i->pagibig_contribution, $i->withholding_tax, $i->bonuses, $i->net_pay,
                ]),
            ],
            default => throw new RuntimeException("Unknown export dataset [{$dataset}]."),
        };
    }

    /** @return array{0: list<string>, 1: list<list<mixed>>} */
    private function serviceRows(int $shopId): array
    {
        $rows = $this->services->exportRows($shopId);

        return [$rows[0], array_slice($rows, 1)];
    }

    /** @return LazyCollection<int, list<mixed>> */
    private function map(LazyCollection $records, callable $row): LazyCollection
    {
        return $records->map($row);
    }

    /** Stops spreadsheet apps from running a cell as a formula (CSV injection). */
    private function safeCell(mixed $value): mixed
    {
        if (is_string($value) && $value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'".$value;
        }

        return $value;
    }
}
