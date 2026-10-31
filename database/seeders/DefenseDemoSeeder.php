<?php

namespace Database\Seeders;

use App\Enums\AccountType;
use App\Models\ActivityLog;
use App\Models\Attendance;
use App\Models\BusinessAgreementAcceptance;
use App\Models\Delivery;
use App\Models\Employee;
use App\Models\EmployeeRole;
use App\Models\EmployeeSchedule;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Inventory;
use App\Models\InventoryCategory;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\OrderComplaint;
use App\Models\OrderRating;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\Promotion;
use App\Models\Rider;
use App\Models\Shop;
use App\Models\ShopOrder;
use App\Models\ShopOrderPayment;
use App\Models\ShopService;
use App\Models\Supplier;
use App\Models\User;
use App\Repositories\BusinessAgreementRepository;
use App\Services\DefaultRoleProvisioner;
use App\Services\InventoryService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Ready-to-present shop for the final defense. One shop on a paid Premium plan with every module
 * unlocked, a login for each role (owner, cashier, attendant, rider, customer) and about two months of
 * believable activity: walk-in and online orders in every status, payments, deliveries, ratings,
 * a complaint, promotions, inventory with a few low-stock items, staff, attendance, payroll,
 * expenses and activity logs.
 *
 * Idempotent: it skips when the demo shop already has orders. Dates are anchored to today, so
 * re-seed on a fresh database shortly before the defense to keep "recent" figures current.
 */
class DefenseDemoSeeder extends Seeder
{
    /** Each role signs in with its own password, formatted as <Role>Password_123. */
    public const OWNER_PASSWORD = 'OwnerPassword_123';

    public const CASHIER_PASSWORD = 'CashierPassword_123';

    public const ATTENDANT_PASSWORD = 'AttendantPassword_123';

    public const RIDER_PASSWORD = 'RiderPassword_123';

    public const WASHER_PASSWORD = 'WasherPassword_123';

    public const CUSTOMER_PASSWORD = 'CustomerPassword_123';

    public const OWNER_EMAIL = 'defense.owner@laundryhub.test';

    public const CASHIER_EMAIL = 'defense.cashier@laundryhub.test';

    public const ATTENDANT_EMAIL = 'defense.attendant@laundryhub.test';

    public const RIDER_EMAIL = 'defense.rider@laundryhub.test';

    public const CUSTOMER_EMAIL = 'defense.customer@laundryhub.test';

    private const WALK_IN_CUSTOMERS = ['Maria Santos', 'Jose Reyes', 'Ana Cruz', 'Carlo Bautista', 'Liza Mendoza', 'Ramon Garcia', 'Joy Villanueva', 'Ben Aquino', 'Grace Pascual', 'Noel Fernandez'];

    private const ADDRESSES = [
        ['12 Mabini St., Poblacion, Imus, Cavite', 14.4303, 120.9365],
        ['88 Aguinaldo Hwy., Tanzang Luma, Imus, Cavite', 14.4210, 120.9402],
        ['5 Palico Road, Palico, Imus, Cavite', 14.4158, 120.9301],
        ['21 Nueno Ave., Anabu, Imus, Cavite', 14.4075, 120.9460],
    ];

    private Carbon $now;

    private Shop $shop;

    private User $owner;

    private User $customer;

    /** @var array<string, ShopService> */
    private array $services = [];

    private ?Rider $rider = null;

    private int $sequence = 0;

    public function run(): void
    {
        mt_srand(2027);
        $this->now = Carbon::now();

        DB::transaction(function () {
            $this->owner = $this->seedOwnerShopAndPlan();

            if (ShopOrder::query()->forShop($this->shop->id)->exists()) {
                $this->command?->info('Defense demo shop already has orders; skipping.');

                return;
            }

            $items = $this->seedInventory();
            $this->seedServices($items);
            $this->seedPromotions();
            $employees = $this->seedEmployees();
            $this->customer = $this->seedCustomer();
            $this->seedWalkInOrders();
            $this->seedOnlineOrders();
            $this->seedStockUsage($items);
            $this->seedAttendance($employees);
            $this->seedPayroll($employees);
            $this->seedExpenses();
            $this->seedActivityLogs($employees);

            $this->command?->info('Defense demo accounts');
            foreach ([
                'Owner' => [self::OWNER_EMAIL, self::OWNER_PASSWORD],
                'Cashier' => [self::CASHIER_EMAIL, self::CASHIER_PASSWORD],
                'Attendant' => [self::ATTENDANT_EMAIL, self::ATTENDANT_PASSWORD],
                'Rider' => [self::RIDER_EMAIL, self::RIDER_PASSWORD],
                'Customer' => [self::CUSTOMER_EMAIL, self::CUSTOMER_PASSWORD],
            ] as $label => [$email, $password]) {
                $this->command?->info("  {$label}: {$email} / {$password}");
            }
        });
    }

    private function seedOwnerShopAndPlan(): User
    {
        $owner = User::firstOrCreate(['email' => self::OWNER_EMAIL], [
            'name' => 'Defense Demo Owner',
            'password' => Hash::make(self::OWNER_PASSWORD),
            'role' => AccountType::ShopOwner->value,
            'email_verified_at' => now(),
            'otp_code' => null,
            'otp_expires_at' => null,
        ]);

        $this->shop = Shop::firstOrCreate(['owner_id' => $owner->id], [
            'shop_name' => 'FreshFold Laundry Hub',
            'ownership_type' => 'sole',
            'phone' => '09171234567',
            'block_street' => '45 Mabini Street',
            'municipality' => 'Imus',
            'barangay' => 'Poblacion',
            'postal_code' => '4103',
            'latitude' => 14.4297,
            'longitude' => 120.9367,
            'offers_pickup' => true,
            'offers_delivery' => true,
            'delivery_fee_mode' => 'fixed',
            'delivery_fixed_fee' => 50,
            'delivery_pricing_set_at' => now(),
            'is_open_24_7' => true,
            'paymongo_secret_key' => config('services.paymongo.demo_secret_key'),
            'paymongo_public_key' => config('services.paymongo.demo_public_key'),
            'status' => 'active',
            'setup_completed_at' => now(),
        ]);

        if ($owner->shop_id === null) {
            $owner->update(['shop_id' => $this->shop->id]);
        }

        app(DefaultRoleProvisioner::class)->provision($this->shop->id);

        $plan = Order::query()->where('user_id', $owner->id)->first();

        if (! $plan) {
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

        // Pre-sign the current business agreement so the owner lands on the dashboard, not the signing page.
        $agreement = app(BusinessAgreementRepository::class)->current();

        if ($agreement) {
            BusinessAgreementAcceptance::query()->firstOrCreate(
                ['business_agreement_id' => $agreement->id, 'shop_id' => $this->shop->id],
                [
                    'user_id' => $owner->id,
                    'order_id' => $plan->id,
                    'business_name' => $this->shop->shop_name,
                    'signer_name' => $owner->name,
                    'signer_role' => 'Owner',
                    'signature_method' => 'typed',
                    'accepted_at' => now(),
                    'ip_address' => '127.0.0.1',
                    'user_agent' => 'Defense demo seeder',
                    'content_hash' => $agreement->content_hash,
                ],
            );
        }

        return $owner;
    }

    /** @return array<string, Inventory> */
    private function seedInventory(): array
    {
        $category = InventoryCategory::create(['shop_id' => $this->shop->id, 'name' => 'Laundry Supplies']);
        $suppliers = [
            'chem' => Supplier::create(['shop_id' => $this->shop->id, 'name' => 'CleanChem Trading', 'lead_time_days' => 2]),
            'pack' => Supplier::create(['shop_id' => $this->shop->id, 'name' => 'Bulk Packaging Co.', 'lead_time_days' => 4]),
            'pro' => Supplier::create(['shop_id' => $this->shop->id, 'name' => 'Pro Laundry Supply', 'lead_time_days' => 6]),
        ];

        $make = fn (string $key, string $name, string $unit, int $qty, int $min, float $price, string $supplier) => [$key => Inventory::create([
            'shop_id' => $this->shop->id,
            'inventory_categories_id' => $category->id,
            'supplier_id' => $suppliers[$supplier]->id,
            'name' => $name,
            'unit' => $unit,
            'quantity' => $qty,
            'min_stock' => $min,
            'unit_price' => $price,
            'status' => 'active',
        ])];

        return [
            ...$make('detergent', 'Detergent Powder', 'sachet', 420, 100, 8.00, 'chem'),
            ...$make('softener', 'Fabric Softener', 'sachet', 300, 80, 6.00, 'chem'),
            ...$make('bags', 'Plastic Bags', 'pcs', 650, 150, 2.50, 'pack'),
            ...$make('stain', 'Stain Remover', 'bottle', 4, 6, 120.00, 'pro'),   // low stock
            ...$make('bleach', 'Bleach', 'bottle', 3, 8, 45.00, 'pro'),          // low stock
            ...$make('hangers', 'Hangers', 'pcs', 180, 50, 7.00, 'pack'),
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

        $make('wash', ['service_name' => 'Wash & Fold', 'pricing_model' => 'per_kg', 'price_per_kg' => 60], ['detergent' => 1, 'softener' => 1, 'bags' => 0.2]);
        $make('premium', ['service_name' => 'Premium Wash & Iron', 'pricing_model' => 'per_kg', 'price_per_kg' => 90, 'estimated_hours' => 36], ['detergent' => 1, 'softener' => 1, 'bags' => 0.2, 'hangers' => 0.5]);
        $make('dry', ['service_name' => 'Dry Cleaning', 'pricing_model' => 'per_piece', 'price_per_piece' => 150, 'estimated_hours' => 48], ['stain' => 0.1, 'bags' => 1, 'hangers' => 1]);
        $make('comforter', ['service_name' => 'Comforter Wash', 'pricing_model' => 'per_bundle', 'bundle_weight_kg' => 5, 'bundle_price' => 350, 'estimated_hours' => 48], ['detergent' => 3, 'softener' => 2, 'bags' => 2]);
    }

    private function seedPromotions(): void
    {
        Promotion::create([
            'shop_id' => $this->shop->id,
            'name' => 'Weekend 10% Off',
            'description' => '10% off orders of ₱300 and above.',
            'type' => 'percentage',
            'value' => 10,
            'min_order_amount' => 300,
            'is_active' => true,
            'starts_at' => $this->now->copy()->subMonth()->toDateString(),
            'ends_at' => $this->now->copy()->addMonths(2)->toDateString(),
        ]);

        Promotion::create([
            'shop_id' => $this->shop->id,
            'name' => '₱50 Off First Online Order',
            'description' => 'Flat ₱50 discount for first-time online customers.',
            'type' => 'fixed',
            'value' => 50,
            'min_order_amount' => 200,
            'is_active' => true,
            'starts_at' => $this->now->copy()->subWeeks(2)->toDateString(),
            'ends_at' => $this->now->copy()->addMonth()->toDateString(),
        ]);

        Promotion::create([
            'shop_id' => $this->shop->id,
            'name' => 'Back-to-School Special',
            'description' => 'Ended promotion kept for history.',
            'type' => 'percentage',
            'value' => 15,
            'is_active' => false,
            'starts_at' => $this->now->copy()->subMonths(4)->toDateString(),
            'ends_at' => $this->now->copy()->subMonths(2)->toDateString(),
        ]);
    }

    /** @return list<Employee> */
    private function seedEmployees(): array
    {
        $roster = [
            ['Carla', 'Mercado', 'Cashier', self::CASHIER_EMAIL, 'cashier', 15000, self::CASHIER_PASSWORD, ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']],
            ['Mark', 'Lopez', 'Washer', self::ATTENDANT_EMAIL, 'washer', 14000, self::ATTENDANT_PASSWORD, ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']],
            ['Rico', 'Tan', 'Driver', self::RIDER_EMAIL, 'driver', 13500, self::RIDER_PASSWORD, ['Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']],
            ['Joy', 'Ramos', 'Washer', 'defense.washer2@laundryhub.test', 'washer', 14000, self::WASHER_PASSWORD, ['Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']],
        ];
        $roles = app(DefaultRoleProvisioner::class);

        return collect($roster)->map(function (array $row, int $index) use ($roles) {
            [$first, $last, $position, $email, $role, $pay, $password, $days] = $row;

            $user = User::firstOrCreate(['email' => $email], [
                'name' => "{$first} {$last}",
                'password' => Hash::make($password),
                'role' => AccountType::Staff->value,
                'shop_id' => $this->shop->id,
                'email_verified_at' => now(),
            ]);

            $employee = Employee::create([
                'user_id' => $user->id,
                'shop_id' => $this->shop->id,
                'employee_id' => 'FF-EMP-'.($index + 1),
                'first_name' => $first,
                'last_name' => $last,
                'email' => $email,
                'phone' => '0917'.mt_rand(1000000, 9999999),
                'position' => $position,
                'employment_type' => 'full_time',
                'pay_rate' => $pay,
                'pay_basis' => 'monthly',
                'hire_date' => $this->now->copy()->subMonths(10 - $index)->toDateString(),
                'status' => 'Active',
                'created_by' => $this->owner->id,
            ]);

            $roles->provisionRole($this->shop->id, $role);
            EmployeeRole::firstOrCreate(['employee_id' => $employee->id, 'role' => $role]);

            foreach ($days as $day) {
                EmployeeSchedule::create(['employee_id' => $employee->id, 'day' => $day, 'start_time' => '08:00', 'end_time' => '17:00']);
            }

            if ($position === 'Driver') {
                $this->rider = Rider::query()->updateOrCreate(
                    ['employee_id' => $employee->id],
                    ['shop_id' => $this->shop->id, 'name' => "{$first} {$last}", 'phone' => $employee->phone, 'vehicle_type' => 'Motorcycle', 'status' => 'active', 'is_online' => true, 'available_since' => now()],
                );
            }

            return $employee;
        })->all();
    }

    private function seedCustomer(): User
    {
        return User::firstOrCreate(['email' => self::CUSTOMER_EMAIL], [
            'name' => 'Defense Demo Customer',
            'password' => Hash::make(self::CUSTOMER_PASSWORD),
            'role' => AccountType::Customer->value,
            'email_verified_at' => now(),
            'otp_code' => null,
            'otp_expires_at' => null,
        ]);
    }

    private function seedWalkInOrders(): void
    {
        $mix = ['wash' => 60, 'premium' => 15, 'dry' => 10, 'comforter' => 15];
        $perWeekday = [1 => 5, 2 => 4, 3 => 4, 4 => 5, 5 => 7, 6 => 9, 7 => 5];

        for ($daysAgo = 60; $daysAgo >= 0; $daysAgo--) {
            $day = $this->now->copy()->startOfDay()->subDays($daysAgo);
            $count = $daysAgo === 0 ? 3 : max(1, $perWeekday[$day->dayOfWeekIso] + mt_rand(-1, 1));

            for ($i = 0; $i < $count; $i++) {
                $createdAt = $daysAgo === 0
                    ? $this->now->copy()->subMinutes(mt_rand(30, 300))
                    : $day->copy()->setTime(8, 0)->addMinutes(mt_rand(0, 600));

                $order = $this->orderFor($this->pick($mix), $createdAt, [
                    'order_source' => 'shop',
                    'customer_name' => self::WALK_IN_CUSTOMERS[array_rand(self::WALK_IN_CUSTOMERS)],
                    'customer_phone' => '0917'.mt_rand(1000000, 9999999),
                    'pickup_type' => 'walk_in',
                ]);

                if ($daysAgo === 0) {
                    $order = [...$order, 'status' => mt_rand(0, 1) ? 'in_progress' : 'pending', 'estimated_completion_at' => $this->now->copy()->addHours(mt_rand(6, 20))];
                    ShopOrder::unguarded(fn () => ShopOrder::create($order));

                    continue;
                }

                if (mt_rand(1, 100) <= 5) {
                    ShopOrder::unguarded(fn () => ShopOrder::create([...$order, 'status' => 'cancelled']));

                    continue;
                }

                $completedAt = $createdAt->copy()->addHours(mt_rand(8, 30))->min($this->now);
                $pickedUp = $completedAt->copy()->addHours(mt_rand(1, 10))->min($this->now);
                $finished = $completedAt->lt($this->now->copy()->subHours(12));
                $paid = $daysAgo > 2 ? mt_rand(1, 100) <= 93 : mt_rand(1, 100) <= 55;
                $method = $this->pick(['cash' => 70, 'gcash' => 20, 'maya' => 10]);

                $record = ShopOrder::unguarded(fn () => ShopOrder::create([
                    ...$order,
                    'status' => $finished ? 'picked_up' : 'completed',
                    'completed_at' => $completedAt,
                    'picked_up_at' => $finished ? $pickedUp : null,
                    'estimated_completion_at' => $createdAt->copy()->addHours(24),
                    'payment_status' => $paid ? 'paid' : 'unpaid',
                    'amount_paid' => $paid ? $order['total_amount'] : 0,
                    'paid_at' => $paid ? $completedAt : null,
                    'payment_method' => $paid ? $method : null,
                ]));

                if ($paid) {
                    $this->recordPayment($record, (float) $order['total_amount'], $method, ShopOrderPayment::SOURCE_SHOP, $completedAt);
                }
            }
        }
    }

    private function seedOnlineOrders(): void
    {
        $scenarios = [
            // [days ago, service, deliver, status, paid, delivery status, rating, complaint]
            [40, 'wash', true, 'picked_up', true, 'delivered', 5, null],
            [34, 'premium', true, 'picked_up', true, 'delivered', 4, null],
            [27, 'comforter', false, 'picked_up', true, 'delivered', 5, null],
            [21, 'wash', true, 'picked_up', true, 'delivered', 3, 'late'],
            [15, 'dry', true, 'picked_up', true, 'delivered', 5, null],
            [10, 'wash', false, 'picked_up', true, 'delivered', 4, null],
            [6, 'premium', true, 'completed', true, 'delivered', null, null],
            [3, 'comforter', true, 'completed', false, 'picked_up', null, null],
            [2, 'wash', true, 'in_progress', false, 'assigned', null, null],
            [1, 'dry', false, 'in_progress', true, 'assigned', null, null],
            [0, 'wash', true, 'pending', false, 'pending', null, null],
            [0, 'premium', false, 'pending', false, null, null, null],
            [12, 'wash', true, 'cancelled', false, 'failed', null, null],
        ];

        foreach ($scenarios as $index => [$daysAgo, $key, $deliver, $status, $paid, $deliveryStatus, $rating, $complaint]) {
            [$address, $lat, $lng] = self::ADDRESSES[$index % count(self::ADDRESSES)];
            $createdAt = $this->now->copy()->subDays($daysAgo)->setTime(9, 0)->addMinutes(mt_rand(0, 300))->min($this->now->copy()->subMinutes(10));

            $order = $this->orderFor($key, $createdAt, [
                'order_source' => 'online',
                'user_id' => $this->customer->id,
                'customer_name' => $this->customer->name,
                'customer_phone' => '09171112222',
                'customer_address' => $address,
                'customer_lat' => $lat,
                'customer_lng' => $lng,
                'pickup_type' => 'pickup',
                'deliver_to_customer' => $deliver,
                'delivery_address' => $deliver ? $address : null,
                'delivery_fee' => $deliver ? 50 : 0,
                'alternate_contact_name' => 'Elena Demo',
                'alternate_contact_phone' => '09173334444',
                'special_instructions' => 'Please separate whites from colors.',
            ]);

            $total = (float) $order['total_amount'] + ($deliver ? 50 : 0);
            $order = [...$order, 'total_amount' => $total];

            if ($status === 'cancelled') {
                $record = ShopOrder::unguarded(fn () => ShopOrder::create([...$order, 'status' => 'cancelled']));
            } else {
                $completedAt = in_array($status, ['completed', 'picked_up'], true) ? $createdAt->copy()->addHours(30)->min($this->now) : null;
                $record = ShopOrder::unguarded(fn () => ShopOrder::create([
                    ...$order,
                    'status' => $status,
                    'in_progress_at' => $status === 'pending' ? null : $createdAt->copy()->addHours(2),
                    'completed_at' => $completedAt,
                    'picked_up_at' => $status === 'picked_up' ? $completedAt->copy()->addHours(4)->min($this->now) : null,
                    'estimated_completion_at' => $createdAt->copy()->addHours(24),
                    'payment_status' => $paid ? 'paid' : 'unpaid',
                    'amount_paid' => $paid ? $total : 0,
                    'paid_at' => $paid ? ($completedAt ?? $createdAt->copy()->addHour()) : null,
                    'payment_method' => $paid ? 'gcash' : null,
                ]));

                if ($paid) {
                    $this->recordPayment($record, $total, 'gcash', ShopOrderPayment::SOURCE_ONLINE, $completedAt ?? $createdAt->copy()->addHour());
                }

                if ($rating !== null) {
                    OrderRating::create([
                        'shop_order_id' => $record->id,
                        'shop_id' => $this->shop->id,
                        'user_id' => $this->customer->id,
                        'rating' => $rating,
                        'comment' => ['Fast and clean, folded neatly!', 'Good service, pickup was on time.', 'Okay, but a little late.', 'Great as always.', 'Will order again.'][$rating - 1] ?? 'Great service.',
                    ]);
                }

                if ($complaint !== null) {
                    OrderComplaint::create([
                        'shop_order_id' => $record->id,
                        'shop_id' => $this->shop->id,
                        'user_id' => $this->customer->id,
                        'type' => $complaint,
                        'description' => 'The order was returned several hours after the promised time.',
                        'status' => 'resolved',
                        'shop_reply' => 'Sorry for the delay. We have added priority handling for your next order.',
                        'replied_by' => $this->owner->id,
                        'replied_at' => $createdAt->copy()->addDays(2),
                        'resolved_at' => $createdAt->copy()->addDays(2),
                    ]);
                }
            }

            if ($deliveryStatus !== null) {
                $this->seedDelivery($record, $deliver, $deliveryStatus, $createdAt, $address, $lat, $lng);
            }
        }

        // An open complaint so the complaints queue is not empty during the demo.
        $open = ShopOrder::query()->forShop($this->shop->id)->where('order_source', 'online')->where('status', 'picked_up')->latest('id')->first();
        if ($open) {
            OrderComplaint::create([
                'shop_order_id' => $open->id,
                'shop_id' => $this->shop->id,
                'user_id' => $this->customer->id,
                'type' => 'stain_damage',
                'description' => 'A small stain remained on one shirt after washing.',
                'status' => 'open',
            ]);
        }
    }

    private function seedDelivery(ShopOrder $order, bool $deliver, string $status, Carbon $createdAt, string $address, float $lat, float $lng): void
    {
        $assigned = in_array($status, ['assigned', 'picked_up', 'delivered'], true);

        Delivery::create([
            'shop_id' => $this->shop->id,
            'shop_order_id' => $order->id,
            'type' => $deliver ? Delivery::TYPE_DELIVERY : Delivery::TYPE_PICKUP,
            'rider_id' => $assigned ? $this->rider?->id : null,
            'customer_name' => $order->customer_name,
            'customer_phone' => $order->customer_phone,
            'customer_email' => $this->customer->email,
            'delivery_address' => $address,
            'destination_lat' => $lat,
            'destination_lng' => $lng,
            'distance_km' => round(mt_rand(15, 60) / 10, 2),
            'delivery_fee' => $deliver ? 50 : 0,
            'courier' => Delivery::COURIER_OWN_RIDER,
            'status' => $status,
            'assigned_at' => $assigned ? $createdAt->copy()->addHour() : null,
            'picked_up_at' => in_array($status, ['picked_up', 'delivered'], true) ? $createdAt->copy()->addHours(3) : null,
            'delivered_at' => $status === 'delivered' ? $createdAt->copy()->addHours(5) : null,
        ]);
    }

    /** @param array<string, mixed> $overrides */
    private function orderFor(string $key, Carbon $createdAt, array $overrides): array
    {
        $service = $this->services[$key];
        $base = [
            'shop_id' => $this->shop->id,
            'service_id' => $service->id,
            'order_number' => sprintf('FF-%s-%04d', $createdAt->format('ymd'), ++$this->sequence),
            'pricing_model' => $service->pricing_model,
            'payment_status' => 'unpaid',
            'amount_paid' => 0,
            'status' => 'pending',
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
            ...$overrides,
        ];

        $quantity = $service->pricing_model === 'per_bundle' ? mt_rand(1, 2) : ($service->pricing_model === 'per_piece' ? mt_rand(2, 6) : mt_rand(3, 9));

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

    private function recordPayment(ShopOrder $order, float $amount, string $method, string $source, Carbon $paidAt): void
    {
        ShopOrderPayment::create([
            'shop_order_id' => $order->id,
            'shop_id' => $this->shop->id,
            'amount' => $amount,
            'method' => $method,
            'reference' => $method === 'cash' ? null : strtoupper($method).'-'.mt_rand(100000, 999999),
            'status' => ShopOrderPayment::STATUS_CONFIRMED,
            'source' => $source,
            'recorded_by' => $source === ShopOrderPayment::SOURCE_SHOP ? $this->owner->id : null,
            'paid_at' => $paidAt,
        ]);
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

    /** @param array<string, Inventory> $items */
    private function seedStockUsage(array $items): void
    {
        $usedIn30Days = ['detergent' => 360, 'softener' => 240, 'bags' => 150, 'stain' => 18, 'bleach' => 30, 'hangers' => 60];

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

    /** @param list<Employee> $employees */
    private function seedAttendance(array $employees): void
    {
        $statuses = ['present' => 82, 'late' => 10, 'absent' => 5, 'half_day' => 3];

        foreach ($employees as $employee) {
            foreach (range(30, 1) as $daysAgo) {
                $date = $this->now->copy()->subDays($daysAgo);

                if ($date->isSunday() && $employee->position !== 'Driver') {
                    continue;
                }

                $status = $this->pick($statuses);
                Attendance::create([
                    'employee_id' => $employee->id,
                    'shop_id' => $this->shop->id,
                    'date' => $date->toDateString(),
                    'status' => $status,
                    'time_in' => $status === 'absent' ? null : ($status === 'late' ? '08:25:00' : '07:55:00'),
                    'time_out' => $status === 'absent' ? null : '17:05:00',
                    'marked_by' => $this->owner->id,
                ]);
            }
        }
    }

    /** @param list<Employee> $employees */
    private function seedPayroll(array $employees): void
    {
        $periods = [
            ['Previous period', $this->now->copy()->subDays(44), $this->now->copy()->subDays(30), 'finalized'],
            ['Last period', $this->now->copy()->subDays(29), $this->now->copy()->subDays(15), 'finalized'],
            ['Current period', $this->now->copy()->subDays(14), $this->now->copy(), 'draft'],
        ];

        foreach ($periods as [$label, $start, $end, $status]) {
            $payroll = Payroll::create([
                'shop_id' => $this->shop->id,
                'period_label' => $label,
                'period_start' => $start->toDateString(),
                'period_end' => $end->toDateString(),
                'status' => $status,
                'created_by' => $this->owner->id,
            ]);

            foreach ($employees as $employee) {
                $net = round((float) $employee->pay_rate / 2, 2);
                PayrollItem::create([
                    'payroll_id' => $payroll->id,
                    'employee_id' => $employee->id,
                    'basic_salary' => $net,
                    'pay_rate' => $employee->pay_rate,
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
            $record('Electricity and water', 2800 + mt_rand(0, 600), $this->now->copy()->subWeeks($weeksAgo)->startOfWeek(), 'Utilities');
        }

        foreach (range(4, 1) as $fortnight) {
            $record('Supplies purchase', 3500 + mt_rand(0, 800), $this->now->copy()->subWeeks($fortnight * 2), 'Supplies');
        }

        foreach (range(2, 0) as $monthsAgo) {
            $record('Shop rent', 12000, $this->now->copy()->subMonths($monthsAgo)->startOfMonth(), 'Rent');
        }

        $record('Dryer maintenance', 4500, $this->now->copy()->subDays(20), 'Repairs');
        $record('Motorcycle fuel and service', 1800, $this->now->copy()->subDays(9), 'Delivery');
    }

    /** @param list<Employee> $employees */
    private function seedActivityLogs(array $employees): void
    {
        $cashier = $employees[0];
        $washer = $employees[1];

        $entries = [
            [$cashier, 'created', 'Employee', $this->owner->id, 30],
            [$employees[2], 'updated', 'Employee', $this->owner->id, 21],
            [$washer, 'status_changed', 'Employee', $this->owner->id, 14],
            [$cashier, 'created', 'Attendance', $cashier->user_id, 7],
            [$washer, 'created', 'Attendance', $washer->user_id, 6],
            [$cashier, 'created', 'Attendance', $cashier->user_id, 3],
            [$washer, 'created', 'Attendance', $washer->user_id, 1],
        ];

        foreach ($entries as [$subject, $action, $module, $performer, $daysAgo]) {
            $log = ActivityLog::create([
                'module' => $module,
                'action' => $action,
                'subject_type' => $subject->getMorphClass(),
                'subject_id' => $subject->getKey(),
                'performed_by' => $performer,
                'shop_id' => $this->shop->id,
                'changes' => $action === 'updated' ? ['position' => ['old' => 'Washer', 'new' => 'Driver']] : null,
            ]);
            $log->forceFill(['created_at' => $this->now->copy()->subDays($daysAgo), 'updated_at' => $this->now->copy()->subDays($daysAgo)])->save();
        }
    }
}
