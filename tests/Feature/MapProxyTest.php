<?php

use App\Enums\AccountType;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

function mapShop(array $overrides = []): Shop
{
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);

    return Shop::create(array_merge([
        'owner_id' => $owner->id,
        'shop_name' => 'Map Laundry',
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Poblacion',
        'postal_code' => '4103',
        'status' => 'active',
        'location_mode' => 'single',
        'setup_completed_at' => now(),
        'offers_delivery' => true,
        'delivery_pricing_set_at' => now(),
        'latitude' => 14.4296,
        'longitude' => 120.9367,
        'delivery_fee_mode' => 'per_km',
        'delivery_base_fee' => 30,
        'delivery_per_km_rate' => 10,
        'delivery_free_km' => 1,
        'delivery_min_fee' => 40,
        'delivery_max_km' => 10,
    ], $overrides));
}

function mapRoutePayload(float $meters = 3200): array
{
    return [
        'code' => 'Ok',
        'routes' => [[
            'distance' => $meters,
            'duration' => 540,
            'geometry' => ['type' => 'LineString', 'coordinates' => [[120.9367, 14.4296], [120.95, 14.45]]],
            'legs' => [['steps' => [['maneuver' => ['instruction' => 'Head north'], 'distance' => 100, 'duration' => 20]]]],
        ]],
    ];
}

beforeEach(function () {
    Cache::flush();
    config()->set('services.mapbox.token', 'pk.test');
    config()->set('services.mapbox.base_url', 'https://mapbox.test');
});

test('guests cannot use the shop-scoped map endpoints', function () {
    $shop = mapShop();

    $this->getJson("/api/shops/{$shop->public_id}/fee-quote?to=120.95,14.45")->assertUnauthorized();
});

test('geocode validates the query and returns places', function () {
    Http::fake(['mapbox.test/*' => Http::response(['features' => [[
        'geometry' => ['coordinates' => [120.94, 14.43]],
        'properties' => ['name' => 'Imus City Hall', 'full_address' => 'Imus City Hall, Imus, Cavite'],
    ]]])]);
    $user = User::factory()->create(['role' => AccountType::Customer->value]);

    $this->actingAs($user)->getJson('/api/geocode?q=ab')->assertUnprocessable()->assertJsonValidationErrors('q');

    $this->actingAs($user)->getJson('/api/geocode?q=imus city hall')
        ->assertOk()
        ->assertJsonPath('results.0.label', 'Imus City Hall, Imus, Cavite')
        ->assertJsonPath('results.0.lat', 14.43);

    Http::assertSent(fn ($request) => ($request['country'] ?? null) === 'ph');
});

test('guests can search addresses and look up a pin, but not without a token', function () {
    Http::fake(['mapbox.test/*' => Http::response(['features' => [[
        'geometry' => ['coordinates' => [120.94, 14.43]],
        'properties' => ['full_address' => 'Poblacion, Imus, Cavite'],
    ]]])]);

    $this->getJson('/api/geocode?q=poblacion imus')->assertOk()->assertJsonPath('results.0.label', 'Poblacion, Imus, Cavite');
    $this->getJson('/api/reverse-geocode?lat=14.43&lng=120.94')->assertOk()->assertJsonPath('address', 'Poblacion, Imus, Cavite');
    $this->getJson('/api/reverse-geocode?lat=999&lng=120.94')->assertUnprocessable();

    config()->set('services.mapbox.token', null);
    $this->getJson('/api/geocode?q=somewhere else')->assertStatus(503)->assertJsonPath('code', 'map_unavailable');
});

test('geocode results are cached for repeated searches', function () {
    Http::fake(['mapbox.test/*' => Http::response(['features' => []])]);
    $user = User::factory()->create(['role' => AccountType::Customer->value]);

    $this->actingAs($user)->getJson('/api/geocode?q=imus')->assertOk();
    $this->actingAs($user)->getJson('/api/geocode?q=Imus')->assertOk();

    Http::assertSentCount(1);
});

test('a shop owner gets a per-km fee quote from the road distance', function () {
    Http::fake(['mapbox.test/*' => Http::response(mapRoutePayload(3200))]);
    $shop = mapShop();

    $this->actingAs($shop->owner)->getJson("/api/shops/{$shop->public_id}/fee-quote?to=120.95,14.45")
        ->assertOk()
        ->assertJsonPath('fee_type', 'per_km')
        ->assertJsonPath('distance_m', 3200)
        ->assertJsonPath('duration_s', 540)
        ->assertJsonPath('fee', 52); // 30 + (3.2 - 1) * 10
});

test('a fixed-rate shop is priced without calling Mapbox', function () {
    Http::fake();
    $shop = mapShop(['delivery_fee_mode' => 'fixed', 'delivery_fixed_fee' => 50]);

    $this->actingAs($shop->owner)->getJson("/api/shops/{$shop->public_id}/fee-quote?to=120.95,14.45")
        ->assertOk()
        ->assertJsonPath('fee', 50)
        ->assertJsonPath('fee_type', 'fixed')
        ->assertJsonPath('distance_m', null);

    Http::assertNothingSent();
});

test('a customer can quote a delivering shop but not one that has delivery off', function () {
    Http::fake(['mapbox.test/*' => Http::response(mapRoutePayload())]);
    $customer = User::factory()->create(['role' => AccountType::Customer->value]);
    $open = mapShop();
    $closed = mapShop(['offers_delivery' => false]);

    $this->actingAs($customer)->getJson("/api/shops/{$open->public_id}/fee-quote?to=120.95,14.45")->assertOk();
    $this->actingAs($customer)->getJson("/api/shops/{$closed->public_id}/fee-quote?to=120.95,14.45")->assertNotFound();
});

test('an owner cannot use another shop and gets the same not-found as a missing shop', function () {
    Http::fake(['mapbox.test/*' => Http::response(mapRoutePayload())]);
    $mine = mapShop();
    $other = mapShop();

    $this->actingAs($mine->owner)->getJson("/api/shops/{$other->public_id}/fee-quote?to=120.95,14.45")->assertNotFound();
    $this->actingAs($mine->owner)->getJson('/api/shops/does-not-exist/route?to=120.95,14.45')->assertNotFound();
    $this->actingAs($mine->owner)->postJson("/api/shops/{$other->public_id}/optimize", [
        'stops' => [['lat' => 14.45, 'lng' => 120.95]],
    ])->assertNotFound();
});

test('a destination beyond the delivery radius returns a clear error', function () {
    Http::fake(['mapbox.test/*' => Http::response(mapRoutePayload(25000))]);
    $shop = mapShop();

    $this->actingAs($shop->owner)->getJson("/api/shops/{$shop->public_id}/fee-quote?to=120.95,14.45")
        ->assertUnprocessable()
        ->assertJsonPath('available', false)
        ->assertJsonFragment(['message' => 'This address is 25.0 km away. Map Laundry delivers within 10 km only.']);
});

test('the destination must be a valid lng,lat pair', function () {
    $shop = mapShop();

    $this->actingAs($shop->owner)->getJson("/api/shops/{$shop->public_id}/route?to=abc")->assertUnprocessable();
    $this->actingAs($shop->owner)->getJson("/api/shops/{$shop->public_id}/route?to=200,14")->assertUnprocessable();
});

test('route returns geometry and steps, and caches per shop', function () {
    Http::fake(['mapbox.test/*' => Http::response(mapRoutePayload())]);
    $shop = mapShop();

    $url = "/api/shops/{$shop->public_id}/route?to=120.95,14.45";
    $this->actingAs($shop->owner)->getJson($url)
        ->assertOk()
        ->assertJsonPath('geometry.type', 'LineString')
        ->assertJsonPath('steps.0.instruction', 'Head north');
    $this->actingAs($shop->owner)->getJson($url)->assertOk();

    Http::assertSentCount(1);
});

test('a missing route returns a clean no-route error', function () {
    Http::fake(['mapbox.test/*' => Http::response(['code' => 'NoRoute', 'routes' => []])]);
    $shop = mapShop();

    $this->actingAs($shop->owner)->getJson("/api/shops/{$shop->public_id}/route?to=120.95,14.45")
        ->assertUnprocessable()
        ->assertJsonPath('code', 'no_route');
});

test('an unreachable map provider returns a 503 without leaking details', function () {
    Http::fake(['mapbox.test/*' => Http::response('boom', 500)]);
    $shop = mapShop();

    $this->actingAs($shop->owner)->getJson("/api/shops/{$shop->public_id}/route?to=120.95,14.45")
        ->assertStatus(503)
        ->assertJsonPath('code', 'map_unavailable');
});

test('optimize returns stops in visiting order starting from the shop', function () {
    Http::fake(['mapbox.test/*' => Http::response([
        'code' => 'Ok',
        'trips' => [[
            'distance' => 9000,
            'duration' => 1500,
            'geometry' => ['type' => 'LineString', 'coordinates' => []],
        ]],
        // input order: shop, A, B, C. Optimised visiting order: shop, C, A, B.
        'waypoints' => [
            ['waypoint_index' => 0],
            ['waypoint_index' => 2],
            ['waypoint_index' => 3],
            ['waypoint_index' => 1],
        ],
    ])]);
    $shop = mapShop();

    $response = $this->actingAs($shop->owner)->postJson("/api/shops/{$shop->public_id}/optimize", [
        'stops' => [
            ['lat' => 14.40, 'lng' => 120.90],
            ['lat' => 14.41, 'lng' => 120.91],
            ['lat' => 14.42, 'lng' => 120.92],
        ],
    ])->assertOk();

    expect($response->json('order'))->toBe([2, 0, 1])
        ->and($response->json('stops.0.sequence'))->toBe(1)
        ->and($response->json('stops.0.index'))->toBe(2);
});

test('optimize validates the number and shape of stops', function () {
    $shop = mapShop();

    $this->actingAs($shop->owner)->postJson("/api/shops/{$shop->public_id}/optimize", ['stops' => []])->assertUnprocessable();
    $this->actingAs($shop->owner)->postJson("/api/shops/{$shop->public_id}/optimize", ['stops' => [['lat' => 'x']]])->assertUnprocessable();
    $this->actingAs($shop->owner)->postJson("/api/shops/{$shop->public_id}/optimize", [
        'stops' => array_fill(0, 12, ['lat' => 14.4, 'lng' => 120.9]),
    ])->assertUnprocessable();
});
