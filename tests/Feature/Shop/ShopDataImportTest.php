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
