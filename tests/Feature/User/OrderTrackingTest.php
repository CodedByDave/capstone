<?php

use App\Enums\AccountType;
use App\Models\Delivery;
use App\Models\Rider;
use App\Models\Shop;
use App\Models\ShopOrder;
use App\Models\ShopService;
use App\Models\User;
use App\Services\CustomerAgreementService;
use App\Services\LogisticsService;

function trackShop(array $overrides = []): Shop
{
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);

    return Shop::create(array_merge([
        'owner_id' => $owner->id,
        'shop_name' => 'Track Laundry',
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Poblacion',
        'postal_code' => '4103',
        'latitude' => 14.4297,
        'longitude' => 120.9367,
        'status' => 'active',
        'offers_pickup' => true,
        'offers_delivery' => true,
        'delivery_pricing_set_at' => now(),
        'setup_completed_at' => now(),
    ], $overrides));
}

function trackCustomer(?Shop $shop = null): User
{
    return User::factory()->create([
        'role' => AccountType::Customer->value,
        'referred_shop_id' => $shop?->id,
    ]);
}

function trackOrder(Shop $shop, User $customer, array $overrides = []): ShopOrder
{
    $service = ShopService::create([
        'shop_id' => $shop->id,
        'service_name' => 'Wash and Fold',
        'is_active' => true,
        'pricing_model' => 'per_kg',
        'price_per_kg' => 60,
    ]);

    return ShopOrder::create(array_merge([
        'shop_id' => $shop->id,
        'user_id' => $customer->id,
        'order_source' => 'online',
        'service_id' => $service->id,
        'order_number' => 'ONL-'.strtoupper(uniqid()),
        'customer_name' => 'Maria Cruz',
        'customer_phone' => '09181234567',
        'customer_address' => '12 Rizal St, Imus',
        'customer_lat' => 14.4301,
        'customer_lng' => 120.9401,
        'pickup_type' => 'pickup',
        'pricing_model' => 'per_kg',
        'price_per_kg' => 60,
        'estimated_weight_kg' => 3,
        'total_amount' => 180,
        'status' => 'in_progress',
    ], $overrides));
}

function trackLeg(ShopOrder $order, string $type, string $status, array $overrides = []): Delivery
{
    return Delivery::create(array_merge([
        'shop_id' => $order->shop_id,
        'shop_order_id' => $order->id,
        'type' => $type,
        'customer_name' => $order->customer_name,
        'delivery_address' => $order->customer_address,
        'destination_lat' => $order->customer_lat,
        'destination_lng' => $order->customer_lng,
        'status' => $status,
    ], $overrides));
}

function trackRider(Shop $shop, ?string $vehicle = 'Honda Click'): Rider
{
    return Rider::create([
        'shop_id' => $shop->id,
        'name' => 'Juan Rider',
        'phone' => '09191234567',
        'vehicle_type' => $vehicle,
        'status' => 'active',
    ]);
}

// ── Access ───────────────────────────────────────────────────────────────────

test('the customer opens the live tracking page for their order', function () {
    $shop = trackShop();
    $customer = trackCustomer($shop);
    $order = trackOrder($shop, $customer);
    trackLeg($order, 'pickup', 'assigned', ['rider_id' => trackRider($shop)->id]);

    $this->actingAs($customer)
        ->get("/user/orders/{$order->payment_public_id}/track")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('user/OrderTracking')
            ->where('tracking.order.order_number', $order->order_number)
            ->where('tracking.active', 'pickup')
            ->where('tracking.pickup.type', 'pickup')
            ->where('tracking.delivery', null));
});

test('another customer gets the same not found as for a missing order', function () {
    $shop = trackShop();
    $order = trackOrder($shop, trackCustomer());
    trackLeg($order, 'pickup', 'assigned');

    $this->actingAs(trackCustomer())->get("/user/orders/{$order->payment_public_id}/track")->assertNotFound();
    $this->actingAs(trackCustomer())->getJson("/user/orders/{$order->payment_public_id}/track/data")->assertNotFound();
    $this->actingAs($order->customer)->get('/user/orders/01JZZZZZZZZZZZZZZZZZZZZZZZ/track')->assertNotFound();
});

test('an order with no rider trip has nothing to track', function () {
    $order = trackOrder(trackShop(), trackCustomer());

    $this->actingAs($order->customer)->get("/user/orders/{$order->payment_public_id}/track")->assertNotFound();
});

test('guests and shop accounts cannot use the tracking page', function () {
    $shop = trackShop();
    $order = trackOrder($shop, trackCustomer());
    trackLeg($order, 'pickup', 'assigned');

    $this->get("/user/orders/{$order->payment_public_id}/track")->assertRedirect('/login');
    $this->actingAs($shop->owner)->get("/user/orders/{$order->payment_public_id}/track")->assertRedirect();
    $this->actingAs($shop->owner)->getJson("/user/orders/{$order->payment_public_id}/track/data")->assertStatus(302);
});

test('a malformed order id is not found', function () {
    $this->actingAs(trackCustomer())->get('/user/orders/12/track')->assertNotFound();
});

// ── Which trip is followed ───────────────────────────────────────────────────

test('the delivery back is followed once it is under way, else the pickup run', function (string $pickup, ?string $delivery, string $expected) {
    $order = trackOrder(trackShop(), trackCustomer());
    trackLeg($order, 'pickup', $pickup);
    if ($delivery !== null) {
        trackLeg($order, 'delivery', $delivery);
    }

    $this->actingAs($order->customer)
        ->getJson("/user/orders/{$order->payment_public_id}/track/data")
        ->assertOk()
        ->assertJsonPath('active', $expected);
})->with([
    'pickup still going, no delivery yet' => ['assigned', null, 'pickup'],
    'pickup done, delivery going' => ['delivered', 'assigned', 'delivery'],
    'both finished' => ['delivered', 'delivered', 'delivery'],
    'pickup finished, nothing else' => ['delivered', null, 'pickup'],
    'pickup failed, delivery waiting' => ['failed', 'pending', 'delivery'],
]);

// ── Payload ──────────────────────────────────────────────────────────────────

test('the live data carries the rider, vehicle, and both stops', function () {
    $shop = trackShop();
    $order = trackOrder($shop, trackCustomer());
    trackLeg($order, 'pickup', 'picked_up', [
        'rider_id' => trackRider($shop, 'Toyota Vios car')->id,
        'rider_lat' => 14.43,
        'rider_lng' => 120.938,
        'rider_located_at' => now(),
    ]);

    $this->actingAs($order->customer)
        ->getJson("/user/orders/{$order->payment_public_id}/track/data")
        ->assertOk()
        ->assertJsonPath('pickup.rider_name', 'Juan Rider')
        ->assertJsonPath('pickup.rider_phone', fn ($phone) => str_ends_with(preg_replace('/\D/', '', $phone), '9191234567'))
        ->assertJsonPath('pickup.vehicle', 'car')
        ->assertJsonPath('pickup.rider.lat', 14.43)
        ->assertJsonPath('pickup.shop.lat', 14.4297)
        ->assertJsonPath('pickup.destination.lng', 120.9401);
});

test('the vehicle drawn on the map follows the rider vehicle text', function (?string $vehicle, string $expected) {
    $rider = Rider::make(['vehicle_type' => $vehicle]);

    expect($rider->vehicleKind())->toBe($expected);
})->with([
    'motorcycle' => ['Motorcycle', 'motorcycle'],
    'a model name' => ['Honda Click 125', 'motorcycle'],
    'nothing entered' => [null, 'motorcycle'],
    'car' => ['Car', 'car'],
    'van' => ['L300 van', 'car'],
    'a sedan' => ['White sedan', 'car'],
]);

test('the rider position is hidden once the trip is not on the road', function (string $status) {
    $shop = trackShop();
    $order = trackOrder($shop, trackCustomer());
    trackLeg($order, 'delivery', $status, [
        'rider_id' => trackRider($shop)->id,
        'rider_lat' => 14.43,
        'rider_lng' => 120.938,
        'rider_located_at' => now(),
    ]);

    $this->actingAs($order->customer)
        ->getJson("/user/orders/{$order->payment_public_id}/track/data")
        ->assertJsonPath('delivery.rider', null);
})->with(['pending', 'delivered', 'failed']);

test('the payload never contains the rider token or internal ids', function () {
    $shop = trackShop();
    $order = trackOrder($shop, trackCustomer());
    $leg = trackLeg($order, 'pickup', 'assigned', ['rider_id' => trackRider($shop)->id]);

    $body = $this->actingAs($order->customer)
        ->getJson("/user/orders/{$order->payment_public_id}/track/data")
        ->assertOk()
        ->content();

    expect($body)->not->toContain($leg->driver_token)
        ->and(json_decode($body, true)['pickup'])->not->toHaveKeys(['id', 'driver_token', 'rider_id', 'shop_id']);
});

test('the order page offers live tracking only when there is a trip', function () {
    $shop = trackShop();
    $order = trackOrder($shop, trackCustomer());

    $this->actingAs($order->customer)
        ->get("/user/orders/{$order->payment_public_id}")
        ->assertInertia(fn ($page) => $page->where('order.can_track', false));

    trackLeg($order, 'pickup', 'assigned');

    $this->actingAs($order->customer)
        ->get("/user/orders/{$order->payment_public_id}")
        ->assertInertia(fn ($page) => $page->where('order.can_track', true));
});

// ── The customer's pin ───────────────────────────────────────────────────────

test('a pickup run heads to the spot the customer pinned', function () {
    $shop = trackShop();
    $order = trackOrder($shop, trackCustomer(), ['status' => 'pending']);

    $run = app(LogisticsService::class)->dispatchPickupRun($order);

    expect($run)->not->toBeNull()
        ->and($run->destination_lat)->toBe(14.4301)
        ->and($run->destination_lng)->toBe(120.9401);
});

test('a pickup run without a pin has no destination yet', function () {
    $shop = trackShop();
    $order = trackOrder($shop, trackCustomer(), ['status' => 'pending', 'customer_lat' => null, 'customer_lng' => null]);

    $run = app(LogisticsService::class)->dispatchPickupRun($order);

    expect($run->destination_lat)->toBeNull()
        ->and($run->destination_lng)->toBeNull();
});

function placeOrderPayload(Shop $shop, array $overrides = []): array
{
    $service = ShopService::create([
        'shop_id' => $shop->id,
        'service_name' => 'Wash and Fold',
        'is_active' => true,
        'pricing_model' => 'per_kg',
        'price_per_kg' => 60,
    ]);

    return array_merge([
        'shop_id' => $shop->id,
        'service_id' => $service->id,
        'customer_name' => 'Maria Cruz',
        'customer_phone' => '09181234567',
        'estimated_weight_kg' => 3,
        'pickup_type' => 'pickup',
        'payment_method' => 'cash',
        'customer_address' => '12 Rizal St, Imus',
        'customer_lat' => 14.4301,
        'customer_lng' => 120.9401,
        'customer_agreement_version' => app(CustomerAgreementService::class)->agreementDataForShopId($shop->id)['version'],
        'customer_agreement_accepted' => true,
    ], $overrides);
}

test('a customer places a pickup order with a pinned location and it reaches the pickup run', function () {
    $shop = trackShop();
    $customer = trackCustomer($shop);

    $this->actingAs($customer)
        ->post('/user/orders', placeOrderPayload($shop))
        ->assertSessionHasNoErrors();

    $order = ShopOrder::sole();
    expect($order->customer_lat)->toBe(14.4301)
        ->and($order->customer_lng)->toBe(120.9401)
        ->and(Delivery::where('shop_order_id', $order->id)->sole()->destination_lat)->toBe(14.4301);
});

test('the pin is optional, and a walk-in order never stores one', function () {
    $shop = trackShop();
    $customer = trackCustomer($shop);

    $this->actingAs($customer)
        ->post('/user/orders', placeOrderPayload($shop, ['customer_lat' => null, 'customer_lng' => null]))
        ->assertSessionHasNoErrors();
    $this->actingAs($customer)
        ->post('/user/orders', placeOrderPayload($shop, ['pickup_type' => 'walk_in']))
        ->assertSessionHasNoErrors();

    expect(ShopOrder::orderBy('id')->get()->map(fn ($order) => $order->customer_lat)->all())->toBe([null, null]);
});

test('the pin must be a complete, valid location', function (array $changes, string $field) {
    $shop = trackShop();

    $this->actingAs(trackCustomer())
        ->post('/user/orders', placeOrderPayload($shop, $changes))
        ->assertSessionHasErrors($field);

    expect(ShopOrder::count())->toBe(0);
})->with([
    'latitude without longitude' => [['customer_lng' => null], 'customer_lng'],
    'longitude without latitude' => [['customer_lat' => null], 'customer_lat'],
    'latitude out of range' => [['customer_lat' => 91], 'customer_lat'],
    'longitude out of range' => [['customer_lng' => -181], 'customer_lng'],
    'not a number' => [['customer_lat' => 'here'], 'customer_lat'],
]);

// ── Review fixes ─────────────────────────────────────────────────────────────

test('a delivery sent to a new address does not inherit the old pickup pin', function () {
    $shop = trackShop();
    $order = trackOrder($shop, trackCustomer(), ['status' => 'completed', 'pickup_type' => 'walk_in']);

    $delivery = app(LogisticsService::class)->sendOrderForDelivery($shop->owner, $order, '99 New St, Bacoor');

    expect($delivery->destination_lat)->toBeNull()
        ->and($delivery->destination_lng)->toBeNull()
        ->and($order->fresh()->customer_lat)->toBeNull();
});

test('sending a delivery to the same address keeps the pin', function () {
    $shop = trackShop();
    $order = trackOrder($shop, trackCustomer(), ['status' => 'completed', 'pickup_type' => 'walk_in']);

    $delivery = app(LogisticsService::class)->sendOrderForDelivery($shop->owner, $order, '12 Rizal St, Imus');

    expect($delivery->destination_lat)->toBe(14.4301);
});

test('a reassigned rider starts without the previous riders position', function () {
    $shop = trackShop();
    $order = trackOrder($shop, trackCustomer());
    $first = trackRider($shop);
    $second = trackRider($shop, 'Car');
    $leg = trackLeg($order, 'delivery', 'assigned', [
        'rider_id' => $first->id,
        'rider_lat' => 14.43,
        'rider_lng' => 120.938,
        'rider_located_at' => now(),
    ]);

    app(LogisticsService::class)->updateStatus($shop->owner, $leg, ['status' => 'assigned', 'rider_id' => $second->id]);

    expect($leg->fresh())
        ->rider_id->toBe($second->id)
        ->rider_lat->toBeNull()
        ->rider_located_at->toBeNull();
});

test('the same rider keeps their position when the status moves on', function () {
    $shop = trackShop();
    $order = trackOrder($shop, trackCustomer());
    $rider = trackRider($shop);
    $leg = trackLeg($order, 'delivery', 'assigned', [
        'rider_id' => $rider->id,
        'rider_lat' => 14.43,
        'rider_lng' => 120.938,
        'rider_located_at' => now(),
    ]);

    app(LogisticsService::class)->updateStatus($shop->owner, $leg, ['status' => 'picked_up', 'rider_id' => $rider->id]);

    expect($leg->fresh()->rider_lat)->toBe(14.43);
});

test('a position that has not updated for a few minutes is flagged as stale', function (int $minutesAgo, bool $stale) {
    $shop = trackShop();
    $order = trackOrder($shop, trackCustomer());
    trackLeg($order, 'delivery', 'picked_up', [
        'rider_id' => trackRider($shop)->id,
        'rider_lat' => 14.43,
        'rider_lng' => 120.938,
        'rider_located_at' => now()->subMinutes($minutesAgo),
    ]);

    $this->actingAs($order->customer)
        ->getJson("/user/orders/{$order->payment_public_id}/track/data")
        ->assertJsonPath('delivery.rider.is_stale', $stale);
})->with([
    'just now' => [0, false],
    'a minute ago' => [1, false],
    'ten minutes ago' => [10, true],
]);

test('the riders phone number is only sent while the rider is on the trip', function (string $status, bool $visible) {
    $shop = trackShop();
    $order = trackOrder($shop, trackCustomer());
    trackLeg($order, 'delivery', $status, ['rider_id' => trackRider($shop)->id]);

    $phone = $this->actingAs($order->customer)
        ->getJson("/user/orders/{$order->payment_public_id}/track/data")
        ->json('delivery.rider_phone');

    expect($phone !== null)->toBe($visible);
})->with([
    'assigned' => ['assigned', true],
    'picked up' => ['picked_up', true],
    'delivered' => ['delivered', false],
    'failed' => ['failed', false],
    'pending' => ['pending', false],
]);

test('a courier booking sends its driver details only while it is under way', function () {
    $shop = trackShop();
    $order = trackOrder($shop, trackCustomer());
    $leg = trackLeg($order, 'delivery', 'picked_up', [
        'courier' => 'lalamove',
        'courier_tracking_url' => 'https://share.lalamove.com/abc',
        'courier_driver_name' => 'Pedro Driver',
        'courier_driver_phone' => '09201234567',
        'courier_vehicle_plate' => 'ABC 123',
    ]);

    $this->actingAs($order->customer)
        ->getJson("/user/orders/{$order->payment_public_id}/track/data")
        ->assertJsonPath('delivery.courier', 'lalamove')
        ->assertJsonPath('delivery.courier_info.driver_name', 'Pedro Driver')
        ->assertJsonPath('delivery.courier_info.vehicle_plate', 'ABC 123')
        ->assertJsonPath('delivery.rider', null);

    $leg->update(['status' => 'delivered']);

    $this->actingAs($order->customer)
        ->getJson("/user/orders/{$order->payment_public_id}/track/data")
        ->assertJsonPath('delivery.courier_info.driver_phone', null);
});

test('when an order somehow has two trips of one type the newest is shown', function () {
    $order = trackOrder(trackShop(), trackCustomer());
    trackLeg($order, 'pickup', 'failed');
    $newest = trackLeg($order, 'pickup', 'assigned');

    $this->actingAs($order->customer)
        ->getJson("/user/orders/{$order->payment_public_id}/track/data")
        ->assertJsonPath('pickup.public_id', $newest->public_id)
        ->assertJsonPath('pickup.status', 'assigned');
});
