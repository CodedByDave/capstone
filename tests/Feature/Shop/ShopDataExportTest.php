<?php

use App\Enums\AccountType;
use App\Http\Middleware\EnforceOwnerPlatformPermissions;
use App\Http\Middleware\EnsureCurrentBusinessAgreementAccepted;
use App\Models\ActivityLog;
use App\Models\Employee;
use App\Models\EmployeeRole;
use App\Models\Expense;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\RolePermission;
use App\Models\Shop;
use App\Models\ShopOrder;
use App\Models\ShopService;
use App\Models\Supplier;
use App\Models\User;
use App\Services\ShopDataExportService;

beforeEach(function () {
    $this->withoutMiddleware([
        EnsureCurrentBusinessAgreementAccepted::class,
        EnforceOwnerPlatformPermissions::class,
    ]);
});

function exportShop(string $name = 'Export Laundry'): Shop
{
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
    $shop = Shop::create([
        'owner_id' => $owner->id,
        'shop_name' => $name,
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Poblacion',
        'postal_code' => '4103',
        'status' => 'active',
        'setup_completed_at' => now(),
    ]);
    Order::create([
        'user_id' => $owner->id,
        'shop_name' => $name,
        'owner_name' => $owner->name,
        'email' => $owner->email,
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Poblacion',
        'postal_code' => '4103',
        'plan_name' => 'Premium',
        'billing_months' => 1,
        'is_trial' => false,
        'total_price' => 2239,
        'status' => 'paid',
        'expires_at' => now()->addMonth(),
    ]);

    return $shop;
}

function exportOrder(Shop $shop, string $customer, array $overrides = []): ShopOrder
{
    $service = ShopService::firstOrCreate(
        ['shop_id' => $shop->id, 'service_name' => 'Wash & Fold'],
        ['is_active' => true, 'pricing_model' => 'per_kg', 'price_per_kg' => 60],
    );

    return ShopOrder::create(array_merge([
        'shop_id' => $shop->id,
        'order_source' => 'shop',
        'service_id' => $service->id,
        'order_number' => 'ORD-'.strtoupper(uniqid()),
        'customer_name' => $customer,
        'pickup_type' => 'walk_in',
        'pricing_model' => 'per_kg',
        'price_per_kg' => 60,
        'estimated_weight_kg' => 5,
        'total_amount' => 300,
        'payment_status' => 'unpaid',
        'amount_paid' => 0,
        'status' => 'pending',
    ], $overrides));
}

function exportStaff(Shop $shop, ?string $module): User
{
    $user = User::factory()->create(['role' => AccountType::Staff->value]);
    $employee = Employee::create([
        'shop_id' => $shop->id,
        'user_id' => $user->id,
        'employee_id' => 'EX-'.$user->id,
        'first_name' => 'Staff',
        'last_name' => 'Member',
        'position' => 'Staff',
        'hire_date' => today(),
        'status' => 'Active',
    ]);
    EmployeeRole::create(['employee_id' => $employee->id, 'role' => 'clerk']);

    if ($module) {
        RolePermission::firstOrCreate(['shop_id' => $shop->id, 'role' => 'clerk', 'module' => $module, 'action' => 'view']);
    }

    return $user;
}

it('downloads the owner shop orders as a CSV and nothing from other shops', function () {
    $shop = exportShop();
    $other = exportShop('Other Laundry');
    exportOrder($shop, 'Maria Mine');
    exportOrder($shop, 'Archived Customer')->delete();
    exportOrder($other, 'Someone Else');

    $response = $this->actingAs($shop->owner)->get(route('shop.data-export.show', 'orders'));

    $response->assertOk()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8')
        ->assertDownload('orders_'.now()->format('Y-m-d').'.csv');

    $csv = $response->streamedContent();
    expect($csv)->toStartWith("\xEF\xBB\xBF")
        ->and($csv)->toContain('"Order number",Customer')
        ->and($csv)->toContain('Maria Mine')
        ->and($csv)->toContain('Archived Customer')
        ->and($csv)->not->toContain('Someone Else');
});

it('neutralises spreadsheet formulas in exported cells', function () {
    $shop = exportShop();
    exportOrder($shop, '=HYPERLINK("http://evil.test")');

    $csv = $this->actingAs($shop->owner)->get(route('shop.data-export.show', 'orders'))->streamedContent();

    expect($csv)->toContain("'=HYPERLINK")
        ->and($csv)->not->toContain(',=HYPERLINK');
});

it('exports expenses, inventory and suppliers for the shop', function () {
    $shop = exportShop();
    $supplier = Supplier::create(['shop_id' => $shop->id, 'name' => 'CleanChem', 'lead_time_days' => 4]);
    Inventory::create(['shop_id' => $shop->id, 'supplier_id' => $supplier->id, 'name' => 'Detergent', 'sku' => 'DET-1', 'unit' => 'pcs', 'quantity' => 9, 'min_stock' => 2]);
    Expense::create(['shop_id' => $shop->id, 'title' => 'Electric bill', 'amount' => 1500, 'expense_date' => today()]);

    $owner = $this->actingAs($shop->owner);

    expect($owner->get(route('shop.data-export.show', 'suppliers'))->streamedContent())->toContain('CleanChem')->toContain('4')
        ->and($owner->get(route('shop.data-export.show', 'inventory'))->streamedContent())->toContain('Detergent')->toContain('CleanChem')
        ->and($owner->get(route('shop.data-export.show', 'expenses'))->streamedContent())->toContain('Electric bill')->toContain('1500');
});

it('returns 404 for an unknown dataset', function () {
    $shop = exportShop();

    $this->actingAs($shop->owner)->get('/shop/export/users')->assertNotFound();
});

it('lets staff export only the datasets their module permission covers', function () {
    $shop = exportShop();
    exportOrder($shop, 'Visible To Operations');
    $operations = exportStaff($shop, 'Operations');

    $this->actingAs($operations)->get('/staff/export/orders')->assertOk();
    $this->actingAs($operations)->get('/staff/export/payroll')->assertRedirect();
    $this->actingAs($operations)->get('/staff/export/expenses')->assertRedirect();
});

it('refuses staff without any module permission', function () {
    $shop = exportShop();
    $nobody = exportStaff($shop, null);

    $this->actingAs($nobody)->get('/staff/export/orders')->assertRedirect();
});

it('gives the owner a zip with every dataset', function () {
    $shop = exportShop();
    exportOrder($shop, 'Zipped Customer');

    $response = $this->actingAs($shop->owner)->get(route('shop.data-export.archive'));

    $response->assertOk()->assertDownload('shop-data-'.now()->format('Y-m-d').'.zip');

    $zip = new ZipArchive;
    $path = $response->baseResponse->getFile()->getPathname();
    expect($zip->open($path))->toBeTrue();

    $names = [];
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $names[] = $zip->getNameIndex($i);
    }
    $orders = $zip->getFromName('orders_'.now()->format('Y-m-d').'.csv');
    $zip->close();

    expect($names)->toHaveCount(count(ShopDataExportService::datasets()))
        ->and($orders)->toContain('Zipped Customer');
});

it('keeps the zip owner only', function () {
    $shop = exportShop();
    $staff = exportStaff($shop, 'Operations');

    $this->actingAs($staff)->get('/shop/export')->assertRedirect();
});

it('records each export in the activity log', function () {
    $shop = exportShop();

    $this->actingAs($shop->owner)->get(route('shop.data-export.show', 'inventory'))->streamedContent();

    expect(ActivityLog::where('shop_id', $shop->id)->where('action', 'exported')->count())->toBe(1);
});
