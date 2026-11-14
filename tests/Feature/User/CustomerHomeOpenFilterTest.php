<?php

use App\Enums\AccountType;
use App\Models\Shop;
use App\Models\ShopService;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

function openFilterShop(string $name, array $overrides = [], ?array $service = null): Shop
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
        'is_open_24_7' => true,
        'operating_days' => [0, 1, 2, 3, 4, 5, 6],
    ], $overrides));

    if ($service) {
        ShopService::create(array_merge([
            'shop_id' => $shop->id,
            'service_name' => 'Wash and Fold',
            'is_active' => true,
            'pricing_model' => 'per_kg',
        ], $service));
    }

    return $shop;
}

/** A shop that is not open on today's weekday, so it is closed right now. */
function closedShop(string $name, array $overrides = [], ?array $service = null): Shop
{
    return openFilterShop($name, array_merge(['operating_days' => [(now()->dayOfWeek + 1) % 7]], $overrides), $service);
}

function openFilterCustomer(): User
{
    return User::factory()->create(['role' => AccountType::Customer->value]);
}

// ─── Home: only shops that are operating ─────────────────────────────────────

test('the home recommends only shops that are open right now', function () {
    openFilterShop('Open Laundry');
    closedShop('Closed Laundry');

    $this->actingAs(openFilterCustomer())
        ->get(route('user.dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->has('nearbyShops', 1)
            ->where('nearbyShops.0.shop_name', 'Open Laundry')
            ->where('nearbyShops.0.is_open', true));
});

test('closed shops do not use up the recommendation slots', function () {
    foreach (range(1, 6) as $i) {
        closedShop("Closed {$i}");
    }
    openFilterShop('Zed Open Laundry');

    $this->actingAs(openFilterCustomer())
        ->get(route('user.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('nearbyShops', 1)
            ->where('nearbyShops.0.shop_name', 'Zed Open Laundry'));
});

test('suspended shops are never recommended', function () {
    openFilterShop('Suspended Laundry', ['status' => 'suspended']);

    $this->actingAs(openFilterCustomer())
        ->get(route('user.dashboard'))
        ->assertInertia(fn (Assert $page) => $page->has('nearbyShops', 0));
});

test('a closed home shop is not recommended on the home but is on the shops page', function () {
    $home = closedShop('My Closed Shop');
    $me = openFilterCustomer();
    $me->update(['referred_shop_id' => $home->id]);

    $this->actingAs($me)->get(route('user.dashboard'))
        ->assertInertia(fn (Assert $page) => $page->has('nearbyShops', 0));

    $this->actingAs($me)->get(route('user.shops'))
        ->assertInertia(fn (Assert $page) => $page->has('shops', 1)->where('shops.0.is_open', false));
});

test('a bad location is rejected', function () {
    $this->actingAs(openFilterCustomer())
        ->get(route('user.dashboard', ['lat' => 'north']))
        ->assertSessionHasErrors('lat');
});

// ─── Shops page: every shop, with sort and filters ───────────────────────────

test('the shops page lists every active shop, open or closed', function () {
    openFilterShop('Open Laundry');
    closedShop('Closed Laundry');
    openFilterShop('Suspended Laundry', ['status' => 'suspended']);

    $this->actingAs(openFilterCustomer())
        ->get(route('user.shops'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('user/Shops')
            ->where('filters', ['sort' => null, 'open' => false])
            ->has('shops', 2)
            ->where('shops', fn ($shops) => collect($shops)->pluck('is_open', 'shop_name')->all() === [
                'Closed Laundry' => false,
                'Open Laundry' => true,
            ]));
});

test('the open now filter lists only operating shops', function () {
    openFilterShop('Open Laundry');
    closedShop('Closed Laundry');

    $this->actingAs(openFilterCustomer())
        ->get(route('user.shops', ['open' => 1]))
        ->assertInertia(fn (Assert $page) => $page
            ->where('filters.open', true)
            ->has('shops', 1)
            ->where('shops.0.shop_name', 'Open Laundry'));
});

test('lowest rate sorts by the headline price and puts shops without a price last', function () {
    openFilterShop('Pricey', service: ['price_per_kg' => 90]);
    openFilterShop('Cheap', service: ['price_per_kg' => 40]);
    openFilterShop('No Services');
    openFilterShop('Middle', service: ['price_per_kg' => 60]);

    $this->actingAs(openFilterCustomer())
        ->get(route('user.shops', ['sort' => 'lowest_rate']))
        ->assertInertia(fn (Assert $page) => $page
            ->where('filters.sort', 'lowest_rate')
            ->where('shops', fn ($shops) => collect($shops)->pluck('shop_name')->all() === ['Cheap', 'Middle', 'Pricey', 'No Services']));
});

test('nearest sorts by distance from the customer', function () {
    openFilterShop('Far Away', ['latitude' => 14.60, 'longitude' => 121.00]);
    openFilterShop('Close By', ['latitude' => 14.4300, 'longitude' => 120.9370]);
    openFilterShop('No Location');

    $this->actingAs(openFilterCustomer())
        ->get(route('user.shops', ['sort' => 'nearest', 'lat' => 14.4296, 'lng' => 120.9367]))
        ->assertInertia(fn (Assert $page) => $page
            ->where('shops', fn ($shops) => collect($shops)->pluck('shop_name')->all() === ['Close By', 'Far Away', 'No Location']));
});

test('shops default to nearest first when the location is known', function () {
    openFilterShop('Far Away', ['latitude' => 14.60, 'longitude' => 121.00]);
    openFilterShop('Close By', ['latitude' => 14.4300, 'longitude' => 120.9370]);

    $this->actingAs(openFilterCustomer())
        ->get(route('user.shops', ['lat' => 14.4296, 'lng' => 120.9367]))
        ->assertInertia(fn (Assert $page) => $page
            ->where('shops.0.shop_name', 'Close By'));
});

test('sort and filters combine with the search', function () {
    openFilterShop('Bubbles Cheap', service: ['price_per_kg' => 40]);
    openFilterShop('Bubbles Pricey', service: ['price_per_kg' => 80]);
    closedShop('Bubbles Closed', service: ['price_per_kg' => 10]);
    openFilterShop('Other Shop', service: ['price_per_kg' => 5]);

    $this->actingAs(openFilterCustomer())
        ->get(route('user.shops', ['search' => 'bubbles', 'sort' => 'lowest_rate', 'open' => 1]))
        ->assertInertia(fn (Assert $page) => $page
            ->where('shops', fn ($shops) => collect($shops)->pluck('shop_name')->all() === ['Bubbles Cheap', 'Bubbles Pricey']));
});

test('an explicit sort is respected even for the customer\'s own shop', function () {
    $home = openFilterShop('Zed Home Shop', service: ['price_per_kg' => 90]);
    openFilterShop('Aaa Cheap Shop', service: ['price_per_kg' => 30]);
    $me = openFilterCustomer();
    $me->update(['referred_shop_id' => $home->id]);

    $this->actingAs($me)
        ->get(route('user.shops', ['sort' => 'lowest_rate']))
        ->assertInertia(fn (Assert $page) => $page
            ->where('shops', fn ($shops) => collect($shops)->pluck('shop_name')->all() === ['Aaa Cheap Shop', 'Zed Home Shop']));

    // Without a sort, the customer's own shop still leads.
    $this->actingAs($me)
        ->get(route('user.shops'))
        ->assertInertia(fn (Assert $page) => $page->where('shops.0.shop_name', 'Zed Home Shop'));
});

test('invalid shop filters are rejected', function () {
    $this->actingAs(openFilterCustomer())
        ->get(route('user.shops', ['sort' => 'cheapest-ever']))
        ->assertSessionHasErrors('sort');
});
