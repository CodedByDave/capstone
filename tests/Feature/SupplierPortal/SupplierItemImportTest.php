<?php

use App\Enums\AccountType;
use App\Http\Middleware\EnforceOwnerPlatformPermissions;
use App\Http\Middleware\EnsureCurrentBusinessAgreementAccepted;
use App\Models\Order;
use App\Models\Shop;
use App\Models\Supplier;
use App\Models\SupplierItem;
use App\Models\User;
use App\Services\Ocr\PriceListParser;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->withoutMiddleware([
        EnsureCurrentBusinessAgreementAccepted::class,
        EnforceOwnerPlatformPermissions::class,
    ]);
    config(['services.ocr_space.key' => 'test-key', 'services.ocr_space.url' => 'https://ocr.test/parse']);
});

/** A supplier with their own login, at a shop that buys from suppliers. */
function importSupplier(string $shopName = 'Import Laundry'): array
{
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
    $shop = Shop::create([
        'owner_id' => $owner->id, 'shop_name' => $shopName, 'phone' => '09171234567',
        'municipality' => 'Imus', 'barangay' => 'Poblacion', 'postal_code' => '4103',
        'status' => 'active', 'setup_completed_at' => now(), 'has_suppliers' => true,
    ]);
    Order::create([
        'user_id' => $owner->id, 'shop_name' => $shopName, 'owner_name' => $owner->name,
        'email' => $owner->email, 'phone' => '09171234567', 'municipality' => 'Imus',
        'barangay' => 'Poblacion', 'postal_code' => '4103', 'plan_name' => 'Premium',
        'billing_months' => 1, 'is_trial' => false, 'total_price' => 2239, 'status' => 'paid',
        'expires_at' => now()->addMonth(),
    ]);
    $login = User::factory()->create(['role' => AccountType::Supplier->value, 'shop_id' => $shop->id]);
    $supplier = Supplier::create([
        'shop_id' => $shop->id, 'user_id' => $login->id, 'name' => "{$shopName} Supplier",
        'email' => $login->email, 'lead_time_days' => 3, 'status' => 'active',
    ]);

    return compact('login', 'supplier');
}

function importCsv(string $contents): UploadedFile
{
    return UploadedFile::fake()->createWithContent('supplies.csv', $contents);
}

function scannedText(?string $text): void
{
    Http::fake(['ocr.test/*' => Http::response($text === null
        ? ['IsErroredOnProcessing' => true, 'ErrorMessage' => ['E502']]
        : ['IsErroredOnProcessing' => false, 'ParsedResults' => [['ParsedText' => $text]]])]);
}

// ── CSV ───────────────────────────────────────────────────────────────────

test('a CSV is read into rows to review without saving anything', function () {
    ['login' => $login] = importSupplier();

    $this->actingAs($login)
        ->post('/supplier-portal/items/import/preview', [
            'csv_file' => importCsv("\xEF\xBB\xBFname,unit,stock_quantity,unit_price\nLiquid Detergent,gallon,24,\"₱1,250.00\"\nFabric Softener,,40,199\n"),
        ])
        ->assertOk()
        ->assertJsonCount(2, 'rows')
        ->assertJsonPath('rows.0.name', 'Liquid Detergent')
        ->assertJsonPath('rows.0.unit_price', 1250)
        ->assertJsonPath('rows.0.existing', false)
        ->assertJsonPath('rows.1.stock_quantity', 40);

    expect(SupplierItem::count())->toBe(0);
});

test('a CSV row whose name is already listed is marked as an update', function () {
    ['login' => $login, 'supplier' => $supplier] = importSupplier();
    SupplierItem::create(['supplier_id' => $supplier->id, 'name' => 'Liquid Detergent', 'unit' => 'gallon', 'stock_quantity' => 5, 'unit_price' => 400]);

    $this->actingAs($login)
        ->post('/supplier-portal/items/import/preview', [
            'csv_file' => importCsv("Product,Price\nliquid detergent,450\n"),
        ])
        ->assertOk()
        ->assertJsonPath('rows.0.existing', true)
        ->assertJsonPath('rows.0.current_price', 400)
        ->assertJsonPath('rows.0.stock_quantity', 5)
        ->assertJsonPath('rows.0.unit', 'gallon');
});

test('a CSV without a name or price column is turned back', function () {
    ['login' => $login] = importSupplier();

    $this->actingAs($login)
        ->postJson('/supplier-portal/items/import/preview', ['csv_file' => importCsv("colour,size\nred,big\n")])
        ->assertStatus(422)
        ->assertJsonValidationErrors('csv_file');
});

// ── Photo (OCR) ───────────────────────────────────────────────────────────

test('a price-list photo is scanned into rows to review', function () {
    ['login' => $login] = importSupplier();
    scannedText("ABC TRADING\nPRICE LIST\nItem Unit Price\nLiquid Detergent 1 gal ₱450.00\nBleach\ngallon\n1,250.00\nCall 0917 123 4567");

    $this->actingAs($login)
        ->post('/supplier-portal/items/import/preview', ['photo' => UploadedFile::fake()->image('list.jpg')])
        ->assertOk()
        ->assertJsonCount(2, 'rows')
        ->assertJsonPath('rows.0.name', 'Liquid Detergent')
        ->assertJsonPath('rows.0.unit', '1 gal')
        ->assertJsonPath('rows.0.unit_price', 450)
        ->assertJsonPath('rows.0.stock_quantity', 0)
        ->assertJsonPath('rows.1.name', 'Bleach')
        ->assertJsonPath('rows.1.unit_price', 1250);

    expect(SupplierItem::count())->toBe(0);
});

test('a photo OCR cannot read, or with no prices, is turned back with a reason', function (?string $text) {
    ['login' => $login] = importSupplier();
    scannedText($text);

    $this->actingAs($login)
        ->postJson('/supplier-portal/items/import/preview', ['photo' => UploadedFile::fake()->image('list.jpg')])
        ->assertStatus(422)
        ->assertJsonValidationErrors('photo');
})->with([
    'unreadable' => [null],
    'no prices' => ["WELCOME\nThank you for your order"],
]);

test('the parser reads split table rows, sizes and units', function () {
    $rows = app(PriceListParser::class)->parse("Powder Soap 25kg sack 1,800\nPlastic Bag per pack 85.50\nHangers x12 P120\nTOTAL 5,000");

    expect($rows)->toBe([
        ['name' => 'Powder Soap', 'unit' => '25kg sack', 'unit_price' => 1800.0],
        ['name' => 'Plastic Bag', 'unit' => 'pack', 'unit_price' => 85.5],
        ['name' => 'Hangers', 'unit' => null, 'unit_price' => 120.0],
    ]);
});

// ── Saving ────────────────────────────────────────────────────────────────

test('saving the reviewed rows adds new supplies and updates listed ones', function () {
    ['login' => $login, 'supplier' => $supplier] = importSupplier();
    $listed = SupplierItem::create(['supplier_id' => $supplier->id, 'name' => 'Bleach', 'unit' => 'gallon', 'stock_quantity' => 5, 'unit_price' => 1100]);

    $this->actingAs($login)
        ->post('/supplier-portal/items/import', ['items' => [
            ['name' => 'Liquid Detergent', 'unit' => '1 gal', 'stock_quantity' => 24, 'unit_price' => 450],
            ['name' => 'BLEACH', 'unit' => 'gallon', 'stock_quantity' => 8, 'unit_price' => 1250],
        ]])
        ->assertRedirect()
        ->assertSessionHas('toast.message', 'Supplies saved: 1 added, 1 updated.');

    expect(SupplierItem::where('supplier_id', $supplier->id)->count())->toBe(2)
        ->and((float) $listed->fresh()->unit_price)->toBe(1250.0)
        ->and((float) $listed->fresh()->stock_quantity)->toBe(8.0);
});

test('each reviewed row needs a name, stock and a non-negative price', function () {
    ['login' => $login] = importSupplier();

    $this->actingAs($login)
        ->post('/supplier-portal/items/import', ['items' => [
            ['name' => '', 'unit' => '', 'stock_quantity' => 'lots', 'unit_price' => -1],
        ]])
        ->assertSessionHasErrors(['items.0.name', 'items.0.stock_quantity', 'items.0.unit_price']);

    expect(SupplierItem::count())->toBe(0);
});

test('an import only touches the signed-in supplier\'s supplies', function () {
    ['login' => $login, 'supplier' => $supplier] = importSupplier('Shop A');
    ['supplier' => $other] = importSupplier('Shop B');
    $theirs = SupplierItem::create(['supplier_id' => $other->id, 'name' => 'Bleach', 'stock_quantity' => 5, 'unit_price' => 1100]);

    $this->actingAs($login)
        ->post('/supplier-portal/items/import', ['items' => [
            ['name' => 'Bleach', 'unit' => null, 'stock_quantity' => 1, 'unit_price' => 1],
        ]])
        ->assertSessionHas('toast.message', 'Supplies saved: 1 added.');

    expect((float) $theirs->fresh()->unit_price)->toBe(1100.0)
        ->and(SupplierItem::where('supplier_id', $supplier->id)->count())->toBe(1);
});

test('only suppliers can import, and the template downloads', function () {
    ['login' => $login] = importSupplier();
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);

    $this->actingAs($owner)
        ->post('/supplier-portal/items/import', ['items' => [['name' => 'X', 'stock_quantity' => 1, 'unit_price' => 1]]])
        ->assertRedirect();
    expect(SupplierItem::count())->toBe(0);

    $response = $this->actingAs($login)->get('/supplier-portal/items/import/template')->assertOk();
    expect($response->streamedContent())->toContain('name,unit,stock_quantity,unit_price');
});
