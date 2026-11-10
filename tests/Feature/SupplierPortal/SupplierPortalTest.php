<?php

use App\Enums\AccountType;
use App\Http\Middleware\EnforceOwnerPlatformPermissions;
use App\Http\Middleware\EnsureCurrentBusinessAgreementAccepted;
use App\Models\Order;
use App\Models\Shop;
use App\Models\Supplier;
use App\Models\SupplierItem;
use App\Models\SupplierPriceList;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->withoutMiddleware([
        EnsureCurrentBusinessAgreementAccepted::class,
        EnforceOwnerPlatformPermissions::class,
    ]);
    Storage::fake('local');
    config(['services.ocr_space.key' => 'test-key', 'services.ocr_space.url' => 'https://ocr.test/parse']);
});

/** A shop with suppliers on, and one supplier of it with their own login. */
function portalSupplier(string $shopName = 'Portal Laundry'): array
{
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
    $shop = Shop::create([
        'owner_id' => $owner->id,
        'shop_name' => $shopName,
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Poblacion',
        'postal_code' => '4103',
        'status' => 'active',
        'setup_completed_at' => now(),
        'has_suppliers' => true,
    ]);
    Order::create([
        'user_id' => $owner->id,
        'shop_name' => $shopName,
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
    $login = User::factory()->create(['role' => AccountType::Supplier->value, 'shop_id' => $shop->id]);
    $supplier = Supplier::create([
        'shop_id' => $shop->id,
        'user_id' => $login->id,
        'name' => "{$shopName} Supplier",
        'email' => $login->email,
        'lead_time_days' => 3,
        'status' => 'active',
    ]);

    return compact('owner', 'shop', 'login', 'supplier');
}

function portalItem(Supplier $supplier, string $name, float $price, float $stock = 10): SupplierItem
{
    return SupplierItem::create([
        'supplier_id' => $supplier->id,
        'name' => $name,
        'unit' => 'gallon',
        'stock_quantity' => $stock,
        'unit_price' => $price,
    ]);
}

function fakeOcr(?string $text): void
{
    Http::fake(['ocr.test/*' => Http::response($text === null
        ? ['IsErroredOnProcessing' => true, 'ErrorMessage' => ['E502']]
        : ['IsErroredOnProcessing' => false, 'ParsedResults' => [['ParsedText' => $text]]])]);
}

// ── Access ─────────────────────────────────────────────────────────────────

test('a supplier logs in to their own dashboard', function () {
    ['login' => $login, 'supplier' => $supplier] = portalSupplier();
    portalItem($supplier, 'Liquid Detergent', 450, 0);
    portalItem($supplier, 'Fabric Softener', 380);

    $this->actingAs($login)->get('/supplier-portal')->assertInertia(fn (Assert $page) => $page
        ->component('supplier/Dashboard')
        ->where('supplier.name', $supplier->name)
        ->where('counts.total', 2)
        ->where('counts.out_of_stock', 1)
        ->has('items.data', 2));
});

test('the dashboard searches and filters the catalog on the server', function () {
    ['login' => $login, 'supplier' => $supplier] = portalSupplier();
    portalItem($supplier, 'Liquid Detergent', 450, 0);
    portalItem($supplier, 'Fabric Softener', 380);

    $this->actingAs($login)->get('/supplier-portal?search=softener')
        ->assertInertia(fn (Assert $page) => $page->has('items.data', 1)->where('items.data.0.name', 'Fabric Softener'));
    $this->actingAs($login)->get('/supplier-portal?stock=out_of_stock')
        ->assertInertia(fn (Assert $page) => $page->has('items.data', 1)->where('items.data.0.name', 'Liquid Detergent'));
    $this->actingAs($login)->get('/supplier-portal?sort_by=unit_price&sort_direction=desc')
        ->assertInertia(fn (Assert $page) => $page->where('items.data.0.name', 'Liquid Detergent'));
});

test('other roles cannot open the supplier dashboard', function () {
    ['owner' => $owner] = portalSupplier();

    $this->actingAs($owner)->get('/supplier-portal')->assertRedirect(route('shop.dashboard'));
});

test('a supplier the shop archived or deactivated is signed out', function () {
    ['login' => $login, 'supplier' => $supplier] = portalSupplier();

    $supplier->update(['status' => 'inactive']);
    $this->actingAs($login)->get('/supplier-portal')->assertRedirect(route('login'));
    $this->assertGuest();

    $supplier->update(['status' => 'active']);
    $supplier->delete();
    $this->actingAs($login)->get('/supplier-portal')->assertRedirect(route('login'));
});

test('a supplier login lands on the supplier dashboard', function () {
    expect(AccountType::dashboardRouteFor(AccountType::Supplier->value))->toBe('supplier-portal.dashboard');
});

// ── Supplies ───────────────────────────────────────────────────────────────

test('a supplier adds, edits and removes their supplies', function () {
    ['login' => $login, 'supplier' => $supplier] = portalSupplier();

    $this->actingAs($login)->post('/supplier-portal/items', [
        'name' => '  Bleach  ',
        'unit' => 'bottle',
        'stock_quantity' => 25,
        'unit_price' => 95.5,
    ])->assertRedirect();

    $item = SupplierItem::firstOrFail();
    expect($item->supplier_id)->toBe($supplier->id)->and($item->name)->toBe('Bleach');

    $this->actingAs($login)->put("/supplier-portal/items/{$item->public_id}", [
        'name' => 'Bleach',
        'unit' => 'bottle',
        'stock_quantity' => 0,
        'unit_price' => 99,
    ])->assertRedirect();
    expect((float) $item->fresh()->unit_price)->toBe(99.0);

    $this->actingAs($login)->delete("/supplier-portal/items/{$item->public_id}")->assertRedirect();
    expect(SupplierItem::count())->toBe(0);
});

test('a supply needs a name, stock and a non-negative price', function () {
    ['login' => $login] = portalSupplier();

    $this->actingAs($login)->post('/supplier-portal/items', [
        'name' => '',
        'stock_quantity' => -1,
        'unit_price' => 'abc',
    ])->assertSessionHasErrors(['name', 'stock_quantity', 'unit_price']);
});

test('a supplier cannot touch another supplier\'s supplies', function () {
    ['login' => $loginA] = portalSupplier('Shop A');
    ['supplier' => $supplierB] = portalSupplier('Shop B');
    $itemB = portalItem($supplierB, 'Detergent', 100);

    $this->actingAs($loginA)->put("/supplier-portal/items/{$itemB->public_id}", [
        'name' => 'Hijacked', 'stock_quantity' => 1, 'unit_price' => 1,
    ])->assertNotFound();
    $this->actingAs($loginA)->delete("/supplier-portal/items/{$itemB->public_id}")->assertNotFound();

    expect($itemB->fresh()->name)->toBe('Detergent');
});

// ── Price list OCR ─────────────────────────────────────────────────────────

test('a price list that matches the supplies is verified', function () {
    ['login' => $login, 'supplier' => $supplier] = portalSupplier();
    portalItem($supplier, 'Liquid Detergent', 450);
    portalItem($supplier, 'Fabric Softener', 380);
    fakeOcr("PRICE LIST\nLiquid Detergent 1 gal ₱450.00\nFabric Softener ₱380.00");

    $this->actingAs($login)->post('/supplier-portal/price-lists', [
        'photo' => UploadedFile::fake()->image('prices.jpg'),
    ])->assertRedirect()->assertSessionHas('toast.type', 'success');

    $priceList = SupplierPriceList::firstOrFail();
    expect($priceList->status)->toBe(SupplierPriceList::STATUS_VERIFIED)
        ->and($priceList->match_results)->toHaveCount(2);
    Storage::disk('local')->assertExists($priceList->photo_path);
});

test('a price list with a different price is flagged but still saved', function () {
    ['login' => $login, 'supplier' => $supplier] = portalSupplier();
    portalItem($supplier, 'Liquid Detergent', 450);
    fakeOcr('Liquid Detergent 480.00');

    $this->actingAs($login)->post('/supplier-portal/price-lists', [
        'photo' => UploadedFile::fake()->image('prices.jpg'),
    ])->assertRedirect()->assertSessionHas('toast.type', 'warning');

    $priceList = SupplierPriceList::firstOrFail();
    expect($priceList->status)->toBe(SupplierPriceList::STATUS_MISMATCH)
        ->and($priceList->match_results[0]['result'])->toBe('price_mismatch')
        ->and($priceList->match_results[0]['found_price'])->toEqual(480);
});

test('a re-check uses the corrected supplies', function () {
    ['login' => $login, 'supplier' => $supplier] = portalSupplier();
    $item = portalItem($supplier, 'Liquid Detergent', 450);
    fakeOcr('Liquid Detergent 480.00');
    $this->actingAs($login)->post('/supplier-portal/price-lists', ['photo' => UploadedFile::fake()->image('p.jpg')]);
    $priceList = SupplierPriceList::firstOrFail();

    $item->update(['unit_price' => 480]);
    $this->actingAs($login)->post("/supplier-portal/price-lists/{$priceList->public_id}/recheck")->assertRedirect();

    expect($priceList->fresh()->status)->toBe(SupplierPriceList::STATUS_VERIFIED);
});

test('a photo OCR cannot read is saved as unreadable', function () {
    ['login' => $login, 'supplier' => $supplier] = portalSupplier();
    portalItem($supplier, 'Liquid Detergent', 450);
    fakeOcr(null);

    $this->actingAs($login)->post('/supplier-portal/price-lists', [
        'photo' => UploadedFile::fake()->image('blurry.jpg'),
    ])->assertRedirect();

    expect(SupplierPriceList::firstOrFail()->status)->toBe(SupplierPriceList::STATUS_UNREADABLE);
});

test('a price list needs supplies to check against and an image file', function () {
    ['login' => $login, 'supplier' => $supplier] = portalSupplier();
    fakeOcr('anything');

    $this->actingAs($login)->post('/supplier-portal/price-lists', [
        'photo' => UploadedFile::fake()->image('prices.jpg'),
    ])->assertSessionHasErrors('photo');

    portalItem($supplier, 'Liquid Detergent', 450);
    $this->actingAs($login)->post('/supplier-portal/price-lists', [
        'photo' => UploadedFile::fake()->create('prices.pdf', 10, 'application/pdf'),
    ])->assertSessionHasErrors('photo');

    expect(SupplierPriceList::count())->toBe(0);
    Http::assertNothingSent();
});

test('price-list photos are private to the supplier and their shop', function () {
    ['login' => $loginA, 'owner' => $ownerA, 'supplier' => $supplierA] = portalSupplier('Shop A');
    ['login' => $loginB, 'owner' => $ownerB] = portalSupplier('Shop B');
    portalItem($supplierA, 'Liquid Detergent', 450);
    fakeOcr('Liquid Detergent 450');
    $this->actingAs($loginA)->post('/supplier-portal/price-lists', ['photo' => UploadedFile::fake()->image('p.jpg')]);
    $priceList = SupplierPriceList::firstOrFail();

    $this->actingAs($loginA)->get("/supplier-portal/price-lists/{$priceList->public_id}/photo")->assertOk();
    $this->actingAs($ownerA)->get("/shop/supplier/{$supplierA->public_id}/price-lists/{$priceList->public_id}/photo")->assertOk();

    $this->actingAs($loginB)->get("/supplier-portal/price-lists/{$priceList->public_id}/photo")->assertNotFound();
    $this->actingAs($loginB)->post("/supplier-portal/price-lists/{$priceList->public_id}/recheck")->assertNotFound();
    $this->actingAs($ownerB)->get("/shop/supplier/{$supplierA->public_id}/price-lists/{$priceList->public_id}/photo")->assertNotFound();
});

test('the shop sees the supplier catalog and the latest price-list check', function () {
    ['owner' => $owner, 'login' => $login, 'supplier' => $supplier] = portalSupplier();
    portalItem($supplier, 'Liquid Detergent', 450);
    fakeOcr('Liquid Detergent 450');
    $this->actingAs($login)->post('/supplier-portal/price-lists', ['photo' => UploadedFile::fake()->image('p.jpg')]);

    $this->actingAs($owner)->get("/shop/supplier/{$supplier->public_id}")->assertInertia(fn (Assert $page) => $page
        ->component('shop/inventory/supplier/Show')
        ->has('supplier.items', 1)
        ->where('supplier.latest_price_list.status', 'verified')
        ->missing('supplier.latest_price_list.ocr_text'));
});

test('the portal closes when the shop turns suppliers off', function () {
    ['login' => $login, 'shop' => $shop] = portalSupplier();

    $shop->update(['has_suppliers' => false]);

    $this->actingAs($login)->get('/supplier-portal')->assertRedirect(route('login'));
});

test('a supplier cannot change their login email or delete their login', function () {
    ['login' => $login] = portalSupplier();

    $this->actingAs($login)->patch('/settings/profile', [
        'name' => 'New Name',
        'email' => 'elsewhere@example.test',
    ])->assertSessionHasErrors('email');

    $this->actingAs($login)->delete('/settings/profile', ['password' => 'password'])->assertForbidden();

    expect($login->fresh())->not->toBeNull()->and($login->fresh()->trashed())->toBeFalse();
});
