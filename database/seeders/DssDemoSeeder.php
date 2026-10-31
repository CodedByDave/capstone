<?php

namespace Database\Seeders;

use App\Enums\AccountType;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\EmployeeSchedule;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Inventory;
use App\Models\InventoryCategory;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\Shop;
use App\Models\ShopOrder;
use App\Models\ShopService;
use App\Models\Supplier;
use App\Models\User;
use App\Services\DefaultRoleProvisioner;
use App\Services\InventoryService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Demo shop whose data trips every Decision Support rule: overdue and unpaid orders, cancellations,
 * a service-heavy sales mix, runaway supply use, a loss month, sagging sales, attendance problems,
 * high payroll, a below-cost service and a staffing mismatch on busy days.
 *
 * Order counts per weekday are fixed (busy Saturdays); everything else is anchored to today, so
 * re-seed after a few days to keep the "last 30 days" figures fresh.
 */
class DssDemoSeeder extends Seeder
{
    public const OWNER_EMAIL = 'davemagno2314+dss@gmail.com';

    public const PASSWORD = 'password123';

    /** Orders per ISO weekday (1 = Monday). */
    private const ORDERS_PER_WEEKDAY = [1 => 9, 2 => 4, 3 => 4, 4 => 5, 5 => 10, 6 => 14, 7 => 7];

    private const CUSTOMERS = ['Maria Santos', 'Jose Reyes', 'Ana Cruz', 'Carlo Bautista', 'Liza Mendoza', 'Ramon Garcia', 'Joy Villanueva', 'Ben Aquino'];

    private Carbon $now;

    private Shop $shop;

    private User $owner;

    /** @var array<string, ShopService> */
    private array $services = [];

    public function run(): void
    {
        mt_srand(2026);
        $this->now = Carbon::now();

        DB::transaction(function () {
            $this->owner = $this->seedOwnerAndShop();

            if ($this->owner->role !== AccountType::ShopOwner->value) {
                $this->command?->warn(self::OWNER_EMAIL.' exists but is not a shop owner account; nothing seeded.');

                return;
            }

            if (ShopOrder::query()->forShop($this->shop->id)->exists()) {
                $this->command?->info('DSS demo shop already has orders; skipping.');

                return;
            }

            $items = $this->seedInventory();
            $this->seedServices($items);
            $this->seedOrders();
            $this->seedStockUsage($items);
            $employees = $this->seedEmployees();
            $this->seedAttendance($employees);
            $this->seedPayroll($employees);
            $this->seedExpenses();

            $this->command?->info('DSS demo owner: '.self::OWNER_EMAIL.($this->owner->wasRecentlyCreated ? ' / '.self::PASSWORD : ' (existing account, password unchanged)'));
        });
    }

    private function seedOwnerAndShop(): User
    {
        // An existing account is reused as is: its name, password and shop details are never overwritten.
        $owner = User::firstOrCreate(['email' => self::OWNER_EMAIL], [
            'name' => 'DSS Demo Owner',
            'password' => Hash::make(self::PASSWORD),
            'role' => AccountType::ShopOwner->value,
            'email_verified_at' => now(),
            'otp_code' => null,
            'otp_expires_at' => null,
        ]);

        if ($owner->role !== AccountType::ShopOwner->value) {
            return $owner;
        }

        $this->shop = Shop::firstOrCreate(['owner_id' => $owner->id], [
            'shop_name' => 'Sparkle Wash Demo',
            'phone' => '09171234567',
            'block_street' => '45 Demo Street',
            'municipality' => 'Imus',
            'barangay' => 'Poblacion',
            'postal_code' => '4103',
            'status' => 'active',
            'setup_completed_at' => now(),
        ]);
        if ($owner->shop_id === null) {
            $owner->update(['shop_id' => $this->shop->id]);
        }

        app(DefaultRoleProvisioner::class)->provision($this->shop->id);

        if (! Order::query()->where('user_id', $owner->id)->exists()) {
            $plan = Order::create([
                'user_id' => $owner->id,
                'shop_name' => $this->shop->shop_name,
                'owner_name' => $owner->name,
                'email' => $owner->email,
                'phone' => '09171234567',
                'municipality' => 'Imus',
                'barangay' => 'Poblacion',
                'postal_code' => '4103',
                'plan_name' => 'Premium',
                'billing_months' => 12,
                'is_trial' => false,
                'total_price' => 2239,
                'status' => 'paid',
                'expires_at' => now()->addYear(),
            ]);

            foreach (['HRM', 'Inventory Management', 'Operations', 'Finance Management', 'Reports & Analytics'] as $module) {
                $plan->modules()->create(['name' => $module, 'price' => 0]);
            }
        }

        return $owner;
    }

    /** @return array<string, Inventory> */
    private function seedInventory(): array
    {
        $category = InventoryCategory::create(['shop_id' => $this->shop->id, 'name' => 'Laundry Supplies']);
        $suppliers = [
            'chem' => Supplier::create(['shop_id' => $this->shop->id, 'name' => 'CleanChem Trading', 'lead_time_days' => 2]),
            'pack' => Supplier::create(['shop_id' => $this->shop->id, 'name' => 'Bulk Packaging Co.', 'lead_time_days' => 5]),
            'pro' => Supplier::create(['shop_id' => $this->shop->id, 'name' => 'Pro Laundry Supply', 'lead_time_days' => 7]),
        ];

        $make = fn (string $key, string $name, string $unit, int $qty, int $min, float $price, string $supplier) => [$key => Inventory::create([
            'shop_id' => $this->shop->id,
            'inventory_categories_id' => $category->id,
            'supplier_id' => $suppliers[$supplier]->id,
            'name' => $name,
            'sku' => 'DSS-'.strtoupper($key),
            'unit' => $unit,
            'quantity' => $qty,
            'min_stock' => $min,
            'unit_price' => $price,
            'status' => 'active',
        ])];

        return [
            ...$make('detergent', 'Detergent Powder', 'sachet', 20, 100, 8.00, 'chem'),
            ...$make('softener', 'Fabric Softener', 'sachet', 40, 50, 6.00, 'chem'),
            ...$make('bags', 'Plastic Bags', 'pcs', 400, 100, 2.50, 'pack'),
            ...$make('stain', 'Stain Remover', 'bottle', 6, 5, 120.00, 'pro'),
            ...$make('bleach', 'Bleach', 'bottle', 0, 5, 45.00, 'pro'),
        ];
    }

    /** @param array<string, Inventory> $items */
    private function seedServices(array $items): void
    {
        $make = function (string $key, array $attributes, array $recipe) use ($items) {
            $service = ShopService::create(['shop_id' => $this->shop->id, 'is_active' => true, 'estimated_hours' => 24, ...$attributes]);

            foreach ($recipe as $item => $quantity) {
                $service->supplies()->attach($items[$item]->id, ['quantity' => $quantity, 'unit' => $items[$item]->unit]);
            }

            $this->services[$key] = $service;
        };

        // Healthy margin (about 76%).
        $make('wash', ['service_name' => 'Wash & Fold', 'pricing_model' => 'per_kg', 'price_per_kg' => 60], ['detergent' => 1, 'softener' => 1, 'bags' => 0.2]);
        // Healthy margin (about 58%).
        $make('dry', ['service_name' => 'Dry Cleaning', 'pricing_model' => 'per_piece', 'price_per_piece' => 150], ['stain' => 0.5, 'bags' => 1]);
        // Priced below its supply cost.
        $make('white', ['service_name' => 'Premium Whitening', 'pricing_model' => 'per_kg', 'price_per_kg' => 40], ['detergent' => 2, 'bleach' => 0.5, 'softener' => 1]);
        // Thin margin (about 16%).
        $make('comforter', ['service_name' => 'Comforter Wash', 'pricing_model' => 'per_bundle', 'bundle_weight_kg' => 5, 'bundle_price' => 180], ['detergent' => 4, 'softener' => 3, 'stain' => 0.8, 'bags' => 2]);
    }

    private function seedOrders(): void
    {
        $mix = ['wash' => 72, 'dry' => 10, 'white' => 8, 'comforter' => 10];
        $monthStart = $this->now->copy()->startOfMonth();
        $sequence = 0;
        $overdueLeft = 4;

        for ($daysAgo = 56; $daysAgo >= 0; $daysAgo--) {
            $day = $this->now->copy()->startOfDay()->subDays($daysAgo);
            $count = max(1, self::ORDERS_PER_WEEKDAY[$day->dayOfWeekIso] + mt_rand(-1, 1));
            $count = $daysAgo === 0 ? min($count, 3) : $count;

            for ($i = 0; $i < $count; $i++) {
                $createdAt = $daysAgo === 0
                    ? $this->now->copy()->subMinutes(mt_rand(20, 300))
                    : $day->copy()->setTime(8, 0)->addMinutes(mt_rand(0, 600));
                $order = $this->orderFor($this->pick($mix), ++$sequence, $createdAt);

                if ($daysAgo <= 1) {
                    $overdue = $daysAgo === 1 && $overdueLeft > 0 && $overdueLeft--;
                    $order = [...$order, ...[
                        'status' => $overdue || mt_rand(0, 1) ? 'in_progress' : 'pending',
                        'estimated_completion_at' => $overdue ? $this->now->copy()->subHours(3) : $this->now->copy()->addHours(mt_rand(3, 20)),
                    ]];
                } elseif (mt_rand(1, 100) <= 18) {
                    $order = [...$order, 'status' => 'cancelled'];
                } else {
                    $completedAt = $createdAt->copy()->addHours(mt_rand(6, 30))->min($this->now);
                    // Collections slow down this month, so sales sag against the previous period.
                    $paid = mt_rand(1, 100) <= ($createdAt->gte($monthStart) ? 50 : 95);
                    $order = [...$order, ...[
                        'status' => 'completed',
                        'completed_at' => $completedAt,
                        'estimated_completion_at' => $createdAt->copy()->addHours(24),
                        'payment_status' => $paid ? 'paid' : 'unpaid',
                        'amount_paid' => $paid ? $order['total_amount'] : 0,
                        'paid_at' => $paid ? $completedAt : null,
                        'payment_method' => $paid ? 'cash' : null,
                    ]];
                }

                ShopOrder::unguarded(fn () => ShopOrder::create($order));
            }
        }
    }

    /** @return array<string, mixed> */
    private function orderFor(string $key, int $sequence, Carbon $createdAt): array
    {
        $service = $this->services[$key];
        $base = [
            'shop_id' => $this->shop->id,
            'order_source' => 'shop',
            'service_id' => $service->id,
            'order_number' => sprintf('DSS-%s-%04d', $createdAt->format('ymd'), $sequence),
            'customer_name' => self::CUSTOMERS[array_rand(self::CUSTOMERS)],
            'pickup_type' => 'walk_in',
            'pricing_model' => $service->pricing_model,
            'payment_status' => 'unpaid',
            'amount_paid' => 0,
            'status' => 'pending',
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ];

        $quantity = $service->pricing_model === 'per_bundle' ? mt_rand(1, 2) : ($service->pricing_model === 'per_piece' ? mt_rand(2, 6) : mt_rand(3, 8));

        return match ($service->pricing_model) {
            'per_piece' => [...$base,
                'price_per_piece' => $service->price_per_piece,
                'piece_count' => $quantity,
                'total_amount' => $quantity * (float) $service->price_per_piece,
            ],
            'per_bundle' => [...$base,
                'bundle_weight_kg' => $service->bundle_weight_kg,
                'bundle_price' => $service->bundle_price,
                'bundle_quantity' => $quantity,
                'total_amount' => $quantity * (float) $service->bundle_price,
            ],
            default => [...$base,
                'price_per_kg' => $service->price_per_kg,
                'estimated_weight_kg' => $quantity,
                'actual_weight_kg' => $quantity,
                'total_amount' => $quantity * (float) $service->price_per_kg,
            ],
        };
    }

    /** @param array<string, int> $weights */
    private function pick(array $weights): string
    {
        $roll = mt_rand(1, array_sum($weights));

        foreach ($weights as $key => $weight) {
            if (($roll -= $weight) <= 0) {
                return $key;
            }
        }

        return array_key_first($weights);
    }

    /**
     * Thirty days of supply use ending at each item's current stock, so days-left and reorder
     * suggestions line up with the stock levels shown.
     *
     * @param  array<string, Inventory>  $items
     */
    private function seedStockUsage(array $items): void
    {
        $usedIn30Days = ['detergent' => 540, 'softener' => 300, 'bags' => 200, 'stain' => 30, 'bleach' => 45];

        foreach ($usedIn30Days as $key => $total) {
            $item = $items[$key];
            $running = $item->quantity + $total;

            foreach (range(29, 0) as $daysAgo) {
                $used = intdiv($total, 30) + ($daysAgo < $total % 30 ? 1 : 0);
                $movement = new InventoryMovement([
                    'inventory_id' => $item->id,
                    'shop_id' => $this->shop->id,
                    'user_id' => $this->owner->id,
                    'type' => 'usage',
                    'quantity' => -$used,
                    'quantity_before' => $running,
                    'quantity_after' => $running - $used,
                    'notes' => 'Daily supply use',
                ]);
                $movement->created_at = $this->now->copy()->subDays($daysAgo)->setTime(17, 0);
                $movement->updated_at = $movement->created_at;
                $movement->save();
                $running -= $used;
            }

            app(InventoryService::class)->alertIfLowStock($item->refresh());
        }
    }

    /** @return list<Employee> */
    private function seedEmployees(): array
    {
        // Weekly schedule: Saturday has one person and Sunday none, midweek is crowded.
        $roster = [
            ['Ana', 'Dela Cruz', 'Shop Manager', ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']],
            ['Mark', 'Lopez', 'Laundry Attendant', ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']],
            ['Joy', 'Ramos', 'Laundry Attendant', ['Tuesday', 'Wednesday', 'Thursday']],
            ['Rico', 'Tan', 'Cashier', ['Tuesday', 'Wednesday', 'Thursday']],
        ];

        return collect($roster)->map(function (array $row, int $index) {
            [$first, $last, $position, $days] = $row;

            $employee = Employee::create([
                'shop_id' => $this->shop->id,
                'employee_id' => 'DSS-EMP-'.($index + 1),
                'first_name' => $first,
                'last_name' => $last,
                'email' => strtolower($first).'.demo@laundryhub.test',
                'position' => $position,
                'employment_type' => 'full_time',
                'pay_rate' => 15000,
                'pay_basis' => 'monthly',
                'hire_date' => $this->now->copy()->subMonths(8)->toDateString(),
                'status' => 'Active',
                'created_by' => $this->owner->id,
            ]);

            foreach ($days as $day) {
                EmployeeSchedule::create(['employee_id' => $employee->id, 'day' => $day, 'start_time' => '08:00', 'end_time' => '17:00']);
            }

            return $employee;
        })->all();
    }

    /** @param list<Employee> $employees */
    private function seedAttendance(array $employees): void
    {
        $statuses = ['present' => 62, 'late' => 24, 'absent' => 10, 'half_day' => 4];

        foreach ($employees as $employee) {
            foreach (range(30, 1) as $daysAgo) {
                $date = $this->now->copy()->subDays($daysAgo);

                if ($date->isSunday()) {
                    continue;
                }

                $status = $this->pick($statuses);
                Attendance::create([
                    'employee_id' => $employee->id,
                    'shop_id' => $this->shop->id,
                    'date' => $date->toDateString(),
                    'status' => $status,
                    'time_in' => $status === 'absent' ? null : ($status === 'late' ? '09:20:00' : '07:55:00'),
                    'time_out' => $status === 'absent' ? null : '17:05:00',
                    'marked_by' => $this->owner->id,
                ]);
            }
        }
    }

    /** @param list<Employee> $employees */
    private function seedPayroll(array $employees): void
    {
        $revenue = (float) ShopOrder::query()
            ->forShop($this->shop->id)
            ->where('paid_at', '>=', $this->now->copy()->subDays(30))
            ->sum('amount_paid');

        // Recent payroll costs about 60% of the last 30 days' collected sales.
        $periods = [
            ['Recent period', $this->now->copy()->subDays(16), $this->now->copy()->subDays(2), round($revenue * 0.6 / count($employees), 2)],
            ['Earlier period', $this->now->copy()->subDays(50), $this->now->copy()->subDays(36), 6000.00],
        ];

        foreach ($periods as [$label, $start, $end, $net]) {
            $payroll = Payroll::create([
                'shop_id' => $this->shop->id,
                'period_label' => $label,
                'period_start' => $start->toDateString(),
                'period_end' => $end->toDateString(),
                'status' => 'finalized',
                'created_by' => $this->owner->id,
            ]);

            foreach ($employees as $employee) {
                PayrollItem::create([
                    'payroll_id' => $payroll->id,
                    'employee_id' => $employee->id,
                    'basic_salary' => $net,
                    'pay_rate' => 15000,
                    'pay_basis' => 'monthly',
                    'days_worked' => 12,
                    'net_pay' => $net,
                ]);
            }
        }
    }

    private function seedExpenses(): void
    {
        $category = fn (string $name) => ExpenseCategory::firstOrCreate(['shop_id' => $this->shop->id, 'name' => $name])->id;
        $record = fn (string $title, float $amount, Carbon $date, string $categoryName) => Expense::create([
            'shop_id' => $this->shop->id,
            'category_id' => $category($categoryName),
            'title' => $title,
            'amount' => $amount,
            'expense_date' => $date->toDateString(),
            'created_by' => $this->owner->id,
        ]);

        foreach (range(8, 1) as $weeksAgo) {
            $record('Electricity and water', 3000, $this->now->copy()->subWeeks($weeksAgo)->startOfWeek(), 'Utilities');
        }

        foreach (range(4, 1) as $fortnight) {
            $record('Supplies purchase', 4000, $this->now->copy()->subWeeks($fortnight * 2), 'Supplies');
        }

        // A big repair at the start of the month puts spending above collected sales.
        $record('Washer motor replacement', 30000, $this->now->copy()->startOfMonth(), 'Repairs');
    }
}
