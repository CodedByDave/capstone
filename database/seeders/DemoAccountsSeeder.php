<?php

namespace Database\Seeders;

use App\Enums\AccountType;
use App\Models\Delivery;
use App\Models\Employee;
use App\Models\EmployeeRole;
use App\Models\EmployeeSchedule;
use App\Models\Inventory;
use App\Models\InventoryCategory;
use App\Models\Order;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Rider;
use App\Models\Shop;
use App\Models\ShopOrder;
use App\Models\ShopOrderPayment;
use App\Models\ShopService;
use App\Models\Supplier;
use App\Models\SupplierItem;
use App\Models\User;
use App\Services\DefaultRoleProvisioner;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * A small demo shop with one login per role and roughly ten records of each kind:
 *
 *   php artisan db:seed --class=DemoAccountsSeeder
 *
 * Shop owner, cashier (staff), rider, customer and supplier. Safe to re-run: the logins are
 * reset to the known password and the shop data is only created once. Local/staging only.
 */
class DemoAccountsSeeder extends Seeder
{
    public const PASSWORD = 'DemoPassword_123';

    public const OWNER = 'demo.owner@laundryhub.test';

    public const CASHIER = 'demo.cashier@laundryhub.test';

    public const RIDER = 'demo.rider@laundryhub.test';

    public const CUSTOMER = 'demo.customer@laundryhub.test';

    public const SUPPLIER = 'demo.supplier@laundryhub.test';

    private Shop $shop;

    private User $owner;

    public function run(): void
    {
        mt_srand(11);

        DB::transaction(function () {
            $this->owner = $this->login(self::OWNER, 'Demo Owner', AccountType::ShopOwner);
            $this->shop = $this->seedShop();
            $cashier = $this->login(self::CASHIER, 'Carla Demo', AccountType::Staff, $this->shop->id);
            $riderUser = $this->login(self::RIDER, 'Rico Demo', AccountType::Staff, $this->shop->id);
            $customer = $this->login(self::CUSTOMER, 'Demo Customer', AccountType::Customer);
            $supplierUser = $this->login(self::SUPPLIER, 'Rhea Demo', AccountType::Supplier, $this->shop->id);

            if (ShopOrder::query()->forShop($this->shop->id)->exists()) {
                $this->command?->info('Demo shop already has data; only logins were reset.');
            } else {
                $services = $this->seedServices();
                $suppliers = $this->seedSupplier($supplierUser);
                $this->seedInventory($suppliers['supplier']);
                $this->seedStaff($cashier, 'Cashier', 'cashier', 'Carla', 'Demo');
                $rider = $this->seedStaff($riderUser, 'Driver', 'driver', 'Rico', 'Demo');
                $this->seedOrders($services, $customer, $rider);
                $this->seedPurchaseOrders($suppliers['supplier'], $suppliers['items']);
            }
        });

        $this->command?->table(['Role', 'Email', 'Password'], [
            ['Shop owner', self::OWNER, self::PASSWORD],
            ['Cashier (staff)', self::CASHIER, self::PASSWORD],
            ['Rider', self::RIDER, self::PASSWORD],
            ['Customer', self::CUSTOMER, self::PASSWORD],
            ['Supplier', self::SUPPLIER, self::PASSWORD],
        ]);
    }

    private function login(string $email, string $name, AccountType $role, ?int $shopId = null): User
    {
        $user = User::withTrashed()->firstOrNew(['email' => $email]);
        $user->forceFill([
            'name' => $name,
            'role' => $role->value,
            'shop_id' => $shopId ?? $user->shop_id,
            'password' => Hash::make(self::PASSWORD),
            'email_verified_at' => $user->email_verified_at ?? now(),
            'otp_code' => null,
            'otp_expires_at' => null,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
            'deleted_at' => null,
        ])->save();

        return $user;
    }

    private function seedShop(): Shop
    {
        $shop = Shop::firstOrCreate(['owner_id' => $this->owner->id], [
            'shop_name' => 'Sunrise Laundry Demo',
            'ownership_type' => 'sole',
            'phone' => '09170001111',
            'block_street' => '10 Rizal Street',
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
            'status' => 'active',
            'setup_completed_at' => now(),
            'has_suppliers' => true,
        ]);

        $this->owner->update(['shop_id' => $shop->id]);
        app(DefaultRoleProvisioner::class)->provision($shop->id);

        if (! Order::query()->where('user_id', $this->owner->id)->exists()) {
            $plan = Order::create([
                'user_id' => $this->owner->id,
                'shop_name' => $shop->shop_name,
                'owner_name' => $this->owner->name,
                'email' => $this->owner->email,
                'phone' => '09170001111',
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

            foreach (['Staff Management', 'Inventory Management', 'Operations', 'Finance Management', 'Reports & Analytics'] as $module) {
                $plan->modules()->create(['name' => $module, 'price' => 0]);
            }
        }

        return $shop;
    }

    /** @return array<string, ShopService> */
    private function seedServices(): array
    {
        $rows = [
            'wash' => ['Wash & Fold', 'per_kg', ['price_per_kg' => 60]],
            'iron' => ['Wash & Iron', 'per_kg', ['price_per_kg' => 90, 'estimated_hours' => 36]],
            'dry' => ['Dry Cleaning', 'per_piece', ['price_per_piece' => 150, 'estimated_hours' => 48]],
            'comforter' => ['Comforter Wash', 'per_bundle', ['bundle_weight_kg' => 5, 'bundle_price' => 350, 'estimated_hours' => 48]],
            'express' => ['Express Wash', 'per_kg', ['price_per_kg' => 100, 'estimated_hours' => 6]],
        ];

        return collect($rows)->map(fn ($row) => ShopService::create([
            'shop_id' => $this->shop->id,
            'service_name' => $row[0],
            'pricing_model' => $row[1],
            'is_active' => true,
            'estimated_hours' => 24,
            ...$row[2],
        ]))->all();
    }

    /** @return array{supplier: Supplier, items: list<SupplierItem>} */
    private function seedSupplier(User $user): array
    {
        $supplier = Supplier::create([
            'shop_id' => $this->shop->id,
            'user_id' => $user->id,
            'name' => 'CleanChem Trading',
            'contact_person' => 'Rhea Demo',
            'email' => self::SUPPLIER,
            'phone' => '09171230001',
            'address' => '14 Nueno Ave., Imus, Cavite',
            'lead_time_days' => 2,
            'status' => 'active',
        ]);

        $catalog = [
            ['Liquid Detergent', 'gallon', 40, 450], ['Fabric Conditioner', 'gallon', 32, 380],
            ['Powder Detergent', '25kg sack', 12, 1800], ['Color-safe Bleach', 'gallon', 0, 320],
            ['Stain Remover', '500ml bottle', 25, 165], ['Plastic Bags', 'pack of 100', 60, 180],
            ['Hangers', 'pack of 50', 45, 350], ['Laundry Baskets', 'piece', 15, 220],
            ['Fabric Softener Sachets', 'box of 50', 30, 300], ['Garment Covers', 'pack of 50', 20, 260],
        ];

        $items = array_map(fn ($row) => SupplierItem::create([
            'supplier_id' => $supplier->id, 'name' => $row[0], 'unit' => $row[1], 'stock_quantity' => $row[2], 'unit_price' => $row[3],
        ]), $catalog);

        return ['supplier' => $supplier, 'items' => $items];
    }

    private function seedInventory(Supplier $supplier): void
    {
        $category = InventoryCategory::create(['shop_id' => $this->shop->id, 'name' => 'Laundry Supplies']);

        // name, unit, quantity, min stock, unit price
        $rows = [
            ['Detergent Powder', 'sachet', 300, 100, 8], ['Fabric Softener', 'sachet', 220, 80, 6],
            ['Plastic Bags', 'pcs', 400, 150, 2.5], ['Stain Remover', 'bottle', 4, 6, 120],
            ['Bleach', 'bottle', 3, 8, 45], ['Hangers', 'pcs', 150, 50, 7],
            ['Garment Covers', 'pcs', 90, 30, 5], ['Laundry Baskets', 'pcs', 12, 5, 220],
            ['Fragrance Beads', 'sachet', 160, 40, 4], ['Labels', 'pcs', 500, 100, 0.5],
        ];

        foreach ($rows as [$name, $unit, $qty, $min, $price]) {
            Inventory::create([
                'shop_id' => $this->shop->id,
                'inventory_categories_id' => $category->id,
                'supplier_id' => $supplier->id,
                'name' => $name,
                'unit' => $unit,
                'quantity' => $qty,
                'min_stock' => $min,
                'unit_price' => $price,
                'status' => 'active',
            ]);
        }
    }

    private function seedStaff(User $user, string $position, string $role, string $first, string $last): Employee
    {
        $employee = Employee::create([
            'user_id' => $user->id,
            'shop_id' => $this->shop->id,
            'employee_id' => 'SR-'.strtoupper(substr($role, 0, 3)).'-1',
            'first_name' => $first,
            'last_name' => $last,
            'email' => $user->email,
            'phone' => '0917'.mt_rand(1000000, 9999999),
            'position' => $position,
            'employment_type' => 'full_time',
            'pay_rate' => 14000,
            'pay_basis' => 'monthly',
            'hire_date' => now()->subMonths(6)->toDateString(),
            'status' => 'Active',
            'created_by' => $this->owner->id,
        ]);

        app(DefaultRoleProvisioner::class)->provisionRole($this->shop->id, $role);
        EmployeeRole::firstOrCreate(['employee_id' => $employee->id, 'role' => $role]);

        foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'] as $day) {
            EmployeeSchedule::create(['employee_id' => $employee->id, 'day' => $day, 'start_time' => '08:00', 'end_time' => '17:00']);
        }

        if ($position === 'Driver') {
            return $employee->setRelation('rider', Rider::updateOrCreate(
                ['employee_id' => $employee->id],
                ['shop_id' => $this->shop->id, 'name' => "{$first} {$last}", 'phone' => $employee->phone, 'vehicle_type' => 'Motorcycle', 'status' => 'active', 'is_online' => true, 'available_since' => now()],
            ));
        }

        return $employee;
    }

    /** @param array<string, ShopService> $services */
    private function seedOrders(array $services, User $customer, Employee $rider): void
    {
        $addresses = [['12 Mabini St., Poblacion, Imus, Cavite', 14.4303, 120.9365], ['88 Aguinaldo Hwy., Imus, Cavite', 14.4210, 120.9402]];
        $names = ['Maria Santos', 'Jose Reyes', 'Ana Cruz', 'Carlo Bautista'];

        // [days ago, service, status, paid] - every order is a walk-in, picked up at the shop
        $scenarios = [
            [20, 'wash', 'picked_up', true], [18, 'comforter', 'picked_up', true], [14, 'iron', 'picked_up', true],
            [11, 'wash', 'picked_up', true], [9, 'dry', 'picked_up', true], [5, 'express', 'completed', false],
            [4, 'iron', 'completed', true], [1, 'wash', 'in_progress', false], [1, 'dry', 'in_progress', false],
            [0, 'wash', 'pending', false],
        ];

        foreach ($scenarios as $i => [$daysAgo, $key, $status, $paid]) {
            $service = $services[$key];
            $at = now()->subDays($daysAgo)->setTime(9, 0)->addMinutes($i * 7)->min(now()->subMinutes(5));
            $qty = $service->pricing_model === 'per_bundle' ? 1 : ($service->pricing_model === 'per_piece' ? 4 : mt_rand(3, 8));

            $pricing = match ($service->pricing_model) {
                'per_piece' => ['price_per_piece' => $service->price_per_piece, 'piece_count' => $qty, 'total' => $qty * (float) $service->price_per_piece],
                'per_bundle' => ['bundle_weight_kg' => $service->bundle_weight_kg, 'bundle_price' => $service->bundle_price, 'bundle_quantity' => $qty, 'total' => $qty * (float) $service->bundle_price],
                default => ['price_per_kg' => $service->price_per_kg, 'estimated_weight_kg' => $qty, 'actual_weight_kg' => $qty, 'total' => $qty * (float) $service->price_per_kg],
            };

            $online = false;
            $source = 'shop';
            $address = $addresses[$i % 2];
            $total = $pricing['total'] + ($online ? 50 : 0);
            unset($pricing['total']);
            $done = in_array($status, ['completed', 'picked_up'], true);
            $method = $online ? 'gcash' : 'cash';

            $order = ShopOrder::unguarded(fn () => ShopOrder::create([
                'shop_id' => $this->shop->id,
                'service_id' => $service->id,
                'order_number' => sprintf('SR-%s-%04d', $at->format('ymd'), $i + 1),
                'pricing_model' => $service->pricing_model,
                'order_source' => $source,
                'user_id' => $online ? $customer->id : null,
                'customer_name' => $online ? $customer->name : $names[$i % 4],
                'customer_phone' => $online ? '09171112222' : '0917'.mt_rand(1000000, 9999999),
                'customer_address' => $online ? $address[0] : null,
                'customer_lat' => $online ? $address[1] : null,
                'customer_lng' => $online ? $address[2] : null,
                'pickup_type' => $online ? 'pickup' : 'walk_in',
                'deliver_to_customer' => false,
                'delivery_address' => $online ? $address[0] : null,
                'delivery_fee' => $online ? 50 : 0,
                ...$pricing,
                'total_amount' => $total,
                'status' => $status,
                'in_progress_at' => $status === 'pending' ? null : $at->copy()->addHours(2),
                'completed_at' => $done ? $at->copy()->addHours(20)->min(now()) : null,
                'picked_up_at' => $status === 'picked_up' ? $at->copy()->addHours(24)->min(now()) : null,
                'estimated_completion_at' => $at->copy()->addHours(24),
                'payment_status' => $paid ? 'paid' : 'unpaid',
                'amount_paid' => $paid ? $total : 0,
                'paid_at' => $paid ? $at->copy()->addHours(20)->min(now()) : null,
                'payment_method' => $paid ? $method : null,
                'created_at' => $at,
                'updated_at' => $at,
            ]));

            if ($paid) {
                ShopOrderPayment::create([
                    'shop_order_id' => $order->id,
                    'shop_id' => $this->shop->id,
                    'amount' => $total,
                    'method' => $method,
                    'reference' => $online ? 'GCASH-'.mt_rand(100000, 999999) : null,
                    'status' => ShopOrderPayment::STATUS_CONFIRMED,
                    'source' => $online ? ShopOrderPayment::SOURCE_ONLINE : ShopOrderPayment::SOURCE_SHOP,
                    'recorded_by' => $online ? null : $this->owner->id,
                    'paid_at' => $at->copy()->addHours(20)->min(now()),
                ]);
            }
        }
    }

    /** @param list<SupplierItem> $items */
    private function seedPurchaseOrders(Supplier $supplier, array $items): void
    {
        $statuses = ['received', 'received', 'received', 'confirmed', 'confirmed', 'sent', 'sent', 'draft', 'declined', 'cancelled'];

        foreach ($statuses as $i => $status) {
            $item = $items[$i % count($items)];
            $qty = mt_rand(2, 8);
            $total = $qty * (float) $item->unit_price;
            $at = now()->subDays(25 - $i * 2);

            $po = PurchaseOrder::create([
                'shop_id' => $this->shop->id,
                'supplier_id' => $supplier->id,
                'po_number' => sprintf('PO-%s-%03d', $at->format('ymd'), $i + 1),
                'status' => $status,
                'total' => $total,
                'expected_date' => $at->copy()->addDays(3)->toDateString(),
                'created_by' => $this->owner->id,
                'sent_at' => $status === 'draft' ? null : $at,
                'confirmed_at' => in_array($status, ['confirmed', 'received'], true) ? $at->copy()->addDay() : null,
                'declined_at' => $status === 'declined' ? $at->copy()->addDay() : null,
                'received_at' => $status === 'received' ? $at->copy()->addDays(3) : null,
                'cancelled_at' => $status === 'cancelled' ? $at->copy()->addDay() : null,
            ]);
            $po->forceFill(['created_at' => $at, 'updated_at' => $at])->save();

            PurchaseOrderItem::create([
                'purchase_order_id' => $po->id,
                'supplier_item_id' => $item->id,
                'name' => $item->name,
                'unit' => $item->unit,
                'quantity' => $qty,
                'unit_price' => $item->unit_price,
                'line_total' => $total,
                'stock_per_unit' => 1,
                'received_quantity' => $status === 'received' ? $qty : 0,
            ]);
        }
    }
}
