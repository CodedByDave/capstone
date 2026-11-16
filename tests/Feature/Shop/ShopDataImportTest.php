<?php

use App\Enums\AccountType;
use App\Http\Middleware\EnforceOwnerPlatformPermissions;
use App\Http\Middleware\EnsureCurrentBusinessAgreementAccepted;
use App\Models\Expense;
use App\Models\InventoryCategory;
use App\Models\Promotion;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\UploadedFile;

beforeEach(function () {
    $this->withoutMiddleware([
        EnsureCurrentBusinessAgreementAccepted::class,
        EnforceOwnerPlatformPermissions::class,
    ]);
});

function dataImportOwner(): User
{
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
    Shop::create([
        'owner_id' => $owner->id,
        'shop_name' => 'Import Laundry',
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Poblacion',
        'postal_code' => '4103',
        'status' => 'active',
        'setup_completed_at' => now(),
    ]);

    return $owner->fresh();
}

function dataImportCsv(string $contents): UploadedFile
{
    return UploadedFile::fake()->createWithContent('import.csv', $contents);
}

it('imports inventory categories', function () {
    $owner = dataImportOwner();

    $this->actingAs($owner)
        ->post('/shop/import/inventory-categories', [
            'csv_file' => dataImportCsv("name,description\nDetergents,Powders\nSoftener,\n"),
        ])
        ->assertOk()
        ->assertJson(['imported' => 2]);

    expect(InventoryCategory::where('shop_id', $owner->shop->id)->pluck('name')->all())
        ->toEqualCanonicalizing(['Detergents', 'Softener']);
});

it('imports nothing when any row is invalid', function () {
    $owner = dataImportOwner();
    InventoryCategory::create(['shop_id' => $owner->shop->id, 'name' => 'Detergents']);

    $this->actingAs($owner)
        ->post('/shop/import/inventory-categories', [
            'csv_file' => dataImportCsv("name,description\ndetergents,dup\nSoftener,ok\n"),
        ])
        ->assertStatus(422)
        ->assertJsonPath('errors.0', 'Row 2: a category named "detergents" already exists.');

    expect(InventoryCategory::where('shop_id', $owner->shop->id)->count())->toBe(1);
});

it('imports promotions and validates their type', function () {
    $owner = dataImportOwner();
    $header = "name,description,type,value,min_order_amount,status,starts_at,ends_at\n";

    $this->actingAs($owner)
        ->post('/shop/import/promotions', ['csv_file' => dataImportCsv($header."Bad,,bogus,10,,Active,,\n")])
        ->assertStatus(422);

    $this->actingAs($owner)
        ->post('/shop/import/promotions', ['csv_file' => dataImportCsv($header."Rainy,,percentage,10,300,Inactive,2026-12-01,2026-12-31\n")])
        ->assertOk()
        ->assertJson(['imported' => 1]);

    $promotion = Promotion::where('shop_id', $owner->shop->id)->first();
    expect($promotion->name)->toBe('Rainy')
        ->and($promotion->is_active)->toBeFalse();
});

it('imports expenses and creates missing categories', function () {
    $owner = dataImportOwner();

    $this->actingAs($owner)
        ->post('/shop/import/expenses', [
            'csv_file' => dataImportCsv("title,amount,expense_date,category,description,reference_number\nElectricity,4500,2026-11-15,Utilities,,OR-1\n"),
        ])
        ->assertOk()
        ->assertJson(['imported' => 1]);

    $expense = Expense::where('shop_id', $owner->shop->id)->with('category')->first();
    expect($expense->title)->toBe('Electricity')
        ->and($expense->category->name)->toBe('Utilities');
});

it('rejects a file with missing columns', function () {
    $owner = dataImportOwner();

    $this->actingAs($owner)
        ->post('/shop/import/expenses', ['csv_file' => dataImportCsv("title,amount\nX,5\n")])
        ->assertStatus(422)
        ->assertJsonPath('errors.0', 'Missing column(s): expense_date, category, description, reference_number.');
});

it('serves a template and rejects unknown datasets', function () {
    $owner = dataImportOwner();

    $this->actingAs($owner)->get('/shop/import/promotions/template')->assertOk();
    $this->actingAs($owner)->get('/shop/import/orders/template')->assertNotFound();
    $this->actingAs($owner)->post('/shop/import/orders', ['csv_file' => dataImportCsv("a\n1\n")])->assertNotFound();
});

it('exports promotions and inventory categories', function () {
    $owner = dataImportOwner();
    Promotion::create(['shop_id' => $owner->shop->id, 'name' => 'Promo', 'type' => 'fixed', 'value' => 20, 'is_active' => true]);
    InventoryCategory::create(['shop_id' => $owner->shop->id, 'name' => 'Detergents']);

    $this->actingAs($owner)->get('/shop/export/promotions')->assertOk();
    $this->actingAs($owner)->get('/shop/export/inventory-categories')->assertOk();
});

it('imports suppliers with a login account each', function () {
    $owner = dataImportOwner();

    $this->actingAs($owner)
        ->post('/shop/import/suppliers', [
            'csv_file' => dataImportCsv("name,contact_person,email,phone,address,lead_time_days,status,notes\nSunrise,Maria,maria@sunrise.example,09171234567,Imus,7,Active,\n"),
        ])
        ->assertOk()
        ->assertJson(['imported' => 1]);

    $supplier = App\Models\Supplier::where('shop_id', $owner->shop->id)->first();
    expect($supplier->name)->toBe('Sunrise')->and($supplier->user_id)->not->toBeNull();

    $this->actingAs($owner)
        ->post('/shop/import/suppliers', [
            'csv_file' => dataImportCsv("name,contact_person,email,phone,address,lead_time_days,status,notes\nAgain,,maria@sunrise.example,,,3,Active,\n"),
        ])
        ->assertStatus(422)
        ->assertJsonPath('errors.0', 'Row 2: Another account already uses this email address.');
});

it('imports inventory items against existing categories and logs initial stock', function () {
    $owner = dataImportOwner();
    InventoryCategory::create(['shop_id' => $owner->shop->id, 'name' => 'Detergents']);
    $header = "name,category,supplier,unit,package_unit,package_size,quantity,min_stock,max_stock,unit_price,status\n";

    $this->actingAs($owner)
        ->post('/shop/import/inventory', ['csv_file' => dataImportCsv($header."Soap,Missing,,ml,bottle,1000,20,5,40,120,Active\n")])
        ->assertStatus(422)
        ->assertJsonPath('errors.0', 'Row 2: The category "Missing" does not exist. Create it first.');

    $this->actingAs($owner)
        ->post('/shop/import/inventory', ['csv_file' => dataImportCsv($header."Soap,detergents,,ml,bottle,1000,20,5,40,120,Active\n")])
        ->assertOk()
        ->assertJson(['imported' => 1]);

    $item = App\Models\Inventory::where('shop_id', $owner->shop->id)->first();
    expect($item->name)->toBe('Soap')->and($item->quantity)->toBe(20);
    expect(App\Models\InventoryMovement::where('inventory_id', $item->id)->count())->toBe(1);
});

it('imports attendance for known employee ids only', function () {
    $owner = dataImportOwner();
    $employee = App\Models\Employee::create([
        'shop_id' => $owner->shop->id,
        'user_id' => User::factory()->create()->id,
        'employee_id' => 'EMP-001',
        'first_name' => 'A',
        'last_name' => 'B',
        'position' => 'Staff',
        'hire_date' => today(),
        'status' => 'Active',
    ]);
    $header = "employee_id,date,status,time_in,time_out,remarks\n";

    $this->actingAs($owner)
        ->post('/shop/import/attendance', ['csv_file' => dataImportCsv($header."NOPE,2026-11-15,present,08:00,17:00,\n")])
        ->assertStatus(422);

    $this->actingAs($owner)
        ->post('/shop/import/attendance', ['csv_file' => dataImportCsv($header."emp-001,2026-11-15,late,08:30,17:00,traffic\n")])
        ->assertOk();

    expect(App\Models\Attendance::where('employee_id', $employee->id)->value('status'))->toBe('late');
});

function ledgerOrder(Shop $shop, string $number, float $total = 300): App\Models\ShopOrder
{
    $service = App\Models\ShopService::firstOrCreate(
        ['shop_id' => $shop->id, 'service_name' => 'Wash & Fold'],
        ['is_active' => true, 'pricing_model' => 'per_kg', 'price_per_kg' => 60],
    );

    return App\Models\ShopOrder::create([
        'shop_id' => $shop->id,
        'order_source' => 'shop',
        'service_id' => $service->id,
        'order_number' => $number,
        'customer_name' => 'Walk In',
        'pickup_type' => 'walk_in',
        'pricing_model' => 'per_kg',
        'price_per_kg' => 60,
        'estimated_weight_kg' => 5,
        'total_amount' => $total,
        'payment_status' => 'unpaid',
        'amount_paid' => 0,
        'status' => 'pending',
    ]);
}

it('imports payments and keeps the order balance in step', function () {
    $owner = dataImportOwner();
    $order = ledgerOrder($owner->shop, 'ORD-1');
    $header = "order_number,amount,method,reference,note\n";

    $this->actingAs($owner)
        ->post('/shop/import/income', ['csv_file' => dataImportCsv($header."ord-1,100,cash,,\nORD-1,50,gcash,GC-1,\n")])
        ->assertOk()
        ->assertJson(['imported' => 2]);

    expect((float) $order->fresh()->amount_paid)->toBe(150.0)
        ->and($order->fresh()->payment_status)->toBe('partial');
});

it('rejects the whole payment file when one payment overpays the order', function () {
    $owner = dataImportOwner();
    $order = ledgerOrder($owner->shop, 'ORD-2', 200);

    $this->actingAs($owner)
        ->post('/shop/import/income', ['csv_file' => dataImportCsv("order_number,amount,method,reference,note\nORD-2,150,cash,,\nORD-2,100,cash,,\n")])
        ->assertStatus(422)
        ->assertJsonPath('errors.0', fn ($m) => str_starts_with($m, 'Row 3:'));

    expect((float) $order->fresh()->amount_paid)->toBe(0.0)
        ->and(App\Models\ShopOrderPayment::count())->toBe(0);
});

it('imports mixed income and expense transactions', function () {
    $owner = dataImportOwner();
    ledgerOrder($owner->shop, 'ORD-3', 200);
    $header = "type,order_number,title,category,amount,method,reference,description,date\n";

    $this->actingAs($owner)
        ->post('/shop/import/transactions', ['csv_file' => dataImportCsv($header."income,ORD-3,,,200,cash,,,2026-11-15\nexpense,,Rent,Fixed,5000,,R-1,,2026-11-01\n")])
        ->assertOk()
        ->assertJson(['imported' => 2]);

    expect(Expense::where('shop_id', $owner->shop->id)->value('title'))->toBe('Rent');
});

it('imports stock movements but never below zero', function () {
    $owner = dataImportOwner();
    $item = App\Models\Inventory::create([
        'shop_id' => $owner->shop->id, 'name' => 'Soap', 'unit' => 'ml', 'package_unit' => 'ml',
        'package_size' => 1, 'quantity' => 10, 'min_stock' => 2, 'status' => 'active',
    ]);
    $header = "item,type,quantity,reference_number,notes\n";

    $this->actingAs($owner)
        ->post('/shop/import/stock-movements', ['csv_file' => dataImportCsv($header."soap,restock,5,,\nSoap,usage,-30,,\n")])
        ->assertStatus(422);
    expect($item->fresh()->quantity)->toBe(10);

    $this->actingAs($owner)
        ->post('/shop/import/stock-movements', ['csv_file' => dataImportCsv($header."soap,restock,5,DR-1,\nSoap,usage,-3,,\n")])
        ->assertOk();
    expect($item->fresh()->quantity)->toBe(12);
});

it('imports purchase orders as drafts grouped by reference', function () {
    $owner = dataImportOwner();
    $supplier = App\Models\Supplier::create(['shop_id' => $owner->shop->id, 'name' => 'Sunrise', 'email' => 's@x.test', 'lead_time_days' => 3, 'status' => 'active']);
    App\Models\SupplierItem::create(['supplier_id' => $supplier->id, 'name' => 'Detergent', 'unit' => 'gal', 'stock_quantity' => 5, 'unit_price' => 100]);
    App\Models\SupplierItem::create(['supplier_id' => $supplier->id, 'name' => 'Bags', 'unit' => 'pack', 'stock_quantity' => 5, 'unit_price' => 40]);

    $this->actingAs($owner)
        ->post('/shop/import/purchase-orders', ['csv_file' => dataImportCsv("reference,supplier,item,quantity,notes\nA,sunrise,Detergent,2,Weekly\nA,Sunrise,Bags,3,\nB,Sunrise,Bags,1,\n")])
        ->assertOk()
        ->assertJson(['imported' => 2]);

    $orders = App\Models\PurchaseOrder::where('shop_id', $owner->shop->id)->get();
    expect($orders)->toHaveCount(2)
        ->and($orders->pluck('status')->unique()->all())->toBe(['draft']);
});

it('lets only the owner import permissions', function () {
    $owner = dataImportOwner();
    App\Models\ShopRole::create(['shop_id' => $owner->shop->id, 'name' => 'clerk', 'is_default' => false]);
    $module = App\Models\Module::firstOrCreate(['name' => 'Operations'], ['description' => 'Ops', 'price' => 0]);

    $this->actingAs($owner)
        ->post('/shop/import/permissions', ['csv_file' => dataImportCsv("role,module,action\nclerk,operations,view\nclerk,Operations,view\n")])
        ->assertOk();

    expect(App\Models\RolePermission::where(['shop_id' => $owner->shop->id, 'role' => 'clerk', 'module' => 'Operations', 'action' => 'view'])->count())->toBe(1);

    $this->actingAs($owner)
        ->post('/shop/import/permissions', ['csv_file' => dataImportCsv("role,module,action\nghost,Operations,view\n")])
        ->assertStatus(422);
});

it('exports every remaining dataset for the owner', function () {
    $owner = dataImportOwner();

    foreach (['employee-logs', 'permissions', 'purchase-orders', 'low-stock-alerts', 'complaints', 'ratings', 'logistics', 'payment-history'] as $dataset) {
        $response = $this->actingAs($owner)->get("/shop/export/{$dataset}")->assertOk();

        // The CSV is generated while the response streams, so read it to run the queries.
        expect($response->streamedContent())->not->toBeEmpty();
    }
});
