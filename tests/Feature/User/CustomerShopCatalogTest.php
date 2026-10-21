<?php

use App\Enums\AccountType;
use App\Models\Shop;
use App\Models\ShopService;
use App\Models\User;
use App\Services\CustomerShopCatalogService;
use Inertia\Testing\AssertableInertia as Assert;

function catalogShop(string $name, array $overrides = []): Shop
{
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);

    $shop = Shop::create(array_merge([
        'owner_id' => $owner->id,
        'shop_name' => $name,
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Poblacion',
        'postal_code' => '4103',
        'status' => 'active',
        'location_mode' => 'single',
        'setup_completed_at' => now(),
    ], $overrides));

    return $shop;
}

function catalogKgService(Shop $shop, float $price, array $overrides = []): ShopService
{
    return ShopService::create(array_merge([
        'shop_id' => $shop->id,
        'service_name' => "Per kg {$price}",
        'is_active' => true,
        'pricing_model' => 'per_kg',
        'price_per_kg' => $price,
    ], $overrides));
}

function catalogBundleService(Shop $shop, float $price, array $overrides = []): ShopService
{
    return ShopService::create(array_merge([
        'shop_id' => $shop->id,
        'service_name' => "Bundle {$price}",
        'is_active' => true,
        'pricing_model' => 'per_bundle',
        'bundle_weight_kg' => 7,
        'bundle_price' => $price,
    ], $overrides));
}

function catalogCustomer(): User
{
    return User::factory()->create(['role' => AccountType::Customer->value]);
}

test('the shop list shows a starting price for per-kg, bundle-only and mixed shops', function () {
    $perKg = catalogShop('A Per Kg');
    catalogKgService($perKg, 80);
    catalogKgService($perKg, 60);

    $bundleOnly = catalogShop('B Bundle Only');
    catalogBundleService($bundleOnly, 210);
    catalogBundleService($bundleOnly, 190);

    $mixed = catalogShop('C Mixed');
    catalogBundleService($mixed, 150);
    catalogKgService($mixed, 70);

    catalogShop('D Empty');

    $this->actingAs(catalogCustomer())
        ->get(route('user.shops'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('user/Shops')
            ->where('shops.0.shop_name', 'A Per Kg')
            ->where('shops.0.starting_price', 60)
            ->where('shops.0.starting_price_unit', 'kg')
            ->where('shops.0.services_count', 2)
            ->where('shops.1.shop_name', 'B Bundle Only')
            ->where('shops.1.starting_price', 190)
            ->where('shops.1.starting_price_unit', 'bundle')
            ->where('shops.1.services_count', 2)
            ->where('shops.2.shop_name', 'C Mixed')
            ->where('shops.2.starting_price', 70)
            ->where('shops.2.starting_price_unit', 'kg')
            ->where('shops.3.shop_name', 'D Empty')
            ->where('shops.3.starting_price', null)
            ->where('shops.3.starting_price_unit', null)
            ->where('shops.3.services_count', 0));
});

test('the shop list leaves out inactive services, archived services and inactive shops', function () {
    $shop = catalogShop('Visible Shop');
    catalogBundleService($shop, 200);
    catalogBundleService($shop, 100, ['is_active' => false]);
    catalogBundleService($shop, 50)->delete();

    $pending = catalogShop('Pending Shop', ['status' => 'pending']);
    catalogKgService($pending, 40);

    $this->actingAs(catalogCustomer())
        ->get(route('user.shops'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('shops', 1)
            ->where('shops.0.shop_name', 'Visible Shop')
            ->where('shops.0.starting_price', 200)
            ->where('shops.0.starting_price_unit', 'bundle')
            ->where('shops.0.services_count', 1));
});

test('the dashboard nearby shops show a bundle starting price', function () {
    $shop = catalogShop('Nearby Bundle', ['latitude' => 14.4, 'longitude' => 120.9]);
    catalogBundleService($shop, 190);

    $this->actingAs(catalogCustomer())
        ->get(route('user.dashboard', ['lat' => 14.4, 'lng' => 120.9]))
        ->assertInertia(fn (Assert $page) => $page
            ->where('nearbyShops.0.shop_name', 'Nearby Bundle')
            ->where('nearbyShops.0.starting_price', 190)
            ->where('nearbyShops.0.starting_price_unit', 'bundle'));
});

test('the shop page returns only active services with their pricing', function () {
    $shop = catalogShop('Detail Shop');
    catalogBundleService($shop, 190);
    catalogKgService($shop, 65);
    catalogKgService($shop, 99, ['is_active' => false]);
    catalogKgService($shop, 88)->delete();

    $this->actingAs(catalogCustomer())
        ->get(route('user.shops.show', $shop))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('user/ShopDetail')
            ->has('services', 2)
            ->where('services.0.pricing_model', 'per_bundle')
            ->where('services.0.bundle_price', '190.00')
            ->where('services.0.bundle_weight_kg', '7.00')
            ->where('services.1.pricing_model', 'per_kg')
            ->where('services.1.price_per_kg', '65.00'));
});

test('the shop page is hidden for an inactive shop', function () {
    $shop = catalogShop('Pending Shop', ['status' => 'pending']);
    catalogKgService($shop, 40);

    $this->actingAs(catalogCustomer())
        ->get(route('user.shops.show', $shop))
        ->assertNotFound();
});

test('the shop page tells the customer how each payment method works', function () {
    $online = catalogShop('Online Shop');
    $online->forceFill(['paymongo_secret_key' => 'sk_test_x', 'paymongo_public_key' => 'pk_test_x'])->save();

    $qr = catalogShop('QR Shop', ['gcash_qr' => 'qr/gcash.png']);

    $none = catalogShop('Plain Shop');

    $customer = catalogCustomer();

    $this->actingAs($customer)->get(route('user.shops.show', $online))
        ->assertInertia(fn (Assert $page) => $page
            ->where('shop.payment_options', ['cash' => 'at_shop', 'gcash' => 'online', 'maya' => 'online']));

    $this->actingAs($customer)->get(route('user.shops.show', $qr))
        ->assertInertia(fn (Assert $page) => $page
            ->where('shop.payment_options', ['cash' => 'at_shop', 'gcash' => 'qr', 'maya' => 'unavailable']));

    $this->actingAs($customer)->get(route('user.shops.show', $none))
        ->assertInertia(fn (Assert $page) => $page
            ->where('shop.payment_options', ['cash' => 'at_shop', 'gcash' => 'unavailable', 'maya' => 'unavailable']));
});

test('the catalog service prefers per-kg and falls back to the cheapest bundle', function () {
    $service = app(CustomerShopCatalogService::class);

    expect($service->startingPrice(collect()))->toBe(['amount' => null, 'unit' => null]);

    $bundles = collect([
        new ShopService(['pricing_model' => 'per_bundle', 'bundle_price' => 300]),
        new ShopService(['pricing_model' => 'per_bundle', 'bundle_price' => 250]),
    ]);
    expect($service->startingPrice($bundles))->toBe(['amount' => 250.0, 'unit' => 'bundle']);

    $mixed = $bundles->push(new ShopService(['pricing_model' => 'per_kg', 'price_per_kg' => 90]));
    expect($service->startingPrice($mixed))->toBe(['amount' => 90.0, 'unit' => 'kg']);
});
