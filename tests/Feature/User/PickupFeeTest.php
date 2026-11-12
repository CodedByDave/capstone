<?php

use App\Enums\AccountType;
use App\Models\Shop;
use App\Models\ShopOrder;
use App\Models\ShopService;
use App\Models\User;
use App\Services\CounterDeliveryFeeService;
use Illuminate\Support\Facades\Http;

/** A shop that collects laundry, priced with a fixed delivery fee unless overridden. */
function pickupFeeShop(array $overrides = []): array
{
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
    $shop = Shop::create(array_merge([
        'owner_id' => $owner->id,
        'shop_name' => 'Pickup Laundry',
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Poblacion',
        'postal_code' => '4103',
        'status' => 'active',
        'location_mode' => 'single',
        'setup_completed_at' => now(),
        'offers_pickup' => true,
        'offers_delivery' => true,
        'delivery_pricing_set_at' => now(),
        'latitude' => 14.4296,
        'longitude' => 120.9367,
        'delivery_fee_mode' => 'fixed',
        'delivery_fixed_fee' => 50,
    ], $overrides));
    $service = ShopService::create([
        'shop_id' => $shop->id,
        'service_name' => 'Wash and Fold',
        'is_active' => true,
        'pricing_model' => 'per_kg',
        'price_per_kg' => 60,
    ]);
    $customer = User::factory()->create(['role' => AccountType::Customer->value]);

    return compact('shop', 'service', 'customer');
}

function pickupOrderPayload(Shop $shop, ShopService $service, array $overrides = []): array
{
    return array_merge([
        'shop_id' => $shop->id,
        'service_id' => $service->id,
        'customer_name' => 'Test Customer',
        'customer_phone' => '09171234567',
        'estimated_weight_kg' => 2,
        'pickup_type' => 'pickup',
        'customer_address' => 'Blk 1 Lot 2, Anabu, Imus, Cavite',
        'alternate_contact_name' => 'Ana Cruz',
        'alternate_contact_phone' => '09181234567',
        'customer_lat' => 14.4012,
        'customer_lng' => 120.9401,
        'payment_method' => 'cash',
        'customer_agreement_version' => config('customer_service_agreement.version'),
        'customer_agreement_accepted' => true,
    ], $overrides);
}

/** Mapbox answers with a fixed road distance in meters. */
function pickupRoadDistance(float $meters): void
{
    config()->set('services.mapbox.token', 'pk.test');
    config()->set('services.mapbox.base_url', 'https://mapbox.test');
    Http::fake(['mapbox.test/*' => Http::response([
        'code' => 'Ok',
        'routes' => [['distance' => $meters, 'duration' => 600, 'geometry' => ['type' => 'LineString', 'coordinates' => []], 'legs' => []]],
    ])]);
}

test('a pickup order is charged the fixed fee after tax and shows it in the billing', function () {
    ['shop' => $shop, 'service' => $service, 'customer' => $customer] = pickupFeeShop();

    $this->actingAs($customer)
        ->post(route('user.orders.store'), pickupOrderPayload($shop, $service))
        ->assertSessionHasNoErrors();

    $order = ShopOrder::where('user_id', $customer->id)->firstOrFail();
    expect((float) $order->pickup_fee)->toBe(50.0)
        ->and((float) $order->total_amount)->toBe(round(120 + (float) $order->tax_amount + 50, 2));

    $this->actingAs($customer)
        ->get(route('user.orders.show', $order->payment_public_id))
        ->assertInertia(fn ($page) => $page->where('order.billing.pickup_fee', 50));
});

test('a walk-in order has no pickup fee', function () {
    ['shop' => $shop, 'service' => $service, 'customer' => $customer] = pickupFeeShop();

    $this->actingAs($customer)
        ->post(route('user.orders.store'), pickupOrderPayload($shop, $service, [
            'pickup_type' => 'walk_in',
            'customer_address' => null,
            'alternate_contact_name' => null,
            'alternate_contact_phone' => null,
            'customer_lat' => null,
            'customer_lng' => null,
        ]))
        ->assertSessionHasNoErrors();

    expect((float) ShopOrder::where('user_id', $customer->id)->value('pickup_fee'))->toBe(0.0);
});

test('pickup stays free while the shop has not set its delivery pricing', function () {
    ['shop' => $shop, 'service' => $service, 'customer' => $customer] = pickupFeeShop([
        'offers_delivery' => false,
        'delivery_pricing_set_at' => null,
    ]);

    $this->actingAs($customer)
        ->post(route('user.orders.store'), pickupOrderPayload($shop, $service))
        ->assertSessionHasNoErrors();

    expect((float) ShopOrder::where('user_id', $customer->id)->value('pickup_fee'))->toBe(0.0);
});

test('a per kilometer pickup fee is priced from the pinned address', function () {
    pickupRoadDistance(4000);
    ['shop' => $shop, 'service' => $service, 'customer' => $customer] = pickupFeeShop([
        'delivery_fee_mode' => 'per_km',
        'delivery_base_fee' => 30,
        'delivery_per_km_rate' => 10,
        'delivery_free_km' => 1,
        'delivery_min_fee' => 40,
        'delivery_max_km' => 10,
    ]);
    $expected = app(CounterDeliveryFeeService::class)->quote($shop->id, 14.4012, 120.9401)['fee'];

    $this->actingAs($customer)
        ->post(route('user.orders.store'), pickupOrderPayload($shop, $service))
        ->assertSessionHasNoErrors();

    expect($expected)->toBeGreaterThan(0)
        ->and((float) ShopOrder::where('user_id', $customer->id)->value('pickup_fee'))->toBe((float) $expected);
});

test('a per kilometer pickup without a pinned address is refused instead of guessed', function () {
    ['shop' => $shop, 'service' => $service, 'customer' => $customer] = pickupFeeShop(['delivery_fee_mode' => 'per_km']);

    $this->actingAs($customer)
        ->post(route('user.orders.store'), pickupOrderPayload($shop, $service, ['customer_lat' => null, 'customer_lng' => null]))
        ->assertSessionHasErrors('customer_address');

    expect(ShopOrder::count())->toBe(0);
});

test('the estimate adds the pickup fee only for a pickup', function () {
    ['shop' => $shop, 'service' => $service, 'customer' => $customer] = pickupFeeShop();
    $query = ['shop_id' => $shop->id, 'service_id' => $service->id, 'estimated_weight_kg' => 2];

    $walkIn = $this->actingAs($customer)->getJson(route('user.orders.estimate', $query))->assertOk()->json();
    $pickup = $this->actingAs($customer)->getJson(route('user.orders.estimate', [...$query, 'pickup_type' => 'pickup']))->assertOk()->json();

    expect((float) $walkIn['pickup_fee'])->toBe(0.0)
        ->and((float) $pickup['pickup_fee'])->toBe(50.0)
        ->and(round((float) $pickup['total'] - (float) $walkIn['total'], 2))->toBe(50.0);
});

test('a per kilometer estimate without a pin explains the fee instead of charging it', function () {
    ['shop' => $shop, 'service' => $service, 'customer' => $customer] = pickupFeeShop(['delivery_fee_mode' => 'per_km']);

    $estimate = $this->actingAs($customer)->getJson(route('user.orders.estimate', [
        'shop_id' => $shop->id,
        'service_id' => $service->id,
        'estimated_weight_kg' => 2,
        'pickup_type' => 'pickup',
    ]))->assertOk()->json();

    expect((float) $estimate['pickup_fee'])->toBe(0.0)
        ->and($estimate['pickup_fee_note'])->toContain('distance');
});
