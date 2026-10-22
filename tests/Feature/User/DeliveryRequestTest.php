<?php

use App\Enums\AccountType;
use App\Models\Delivery;
use App\Models\Rider;
use App\Models\Shop;
use App\Models\ShopOrder;
use App\Models\ShopService;
use App\Models\User;
use App\Notifications\DeliveryNotification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;

function dlvShop(array $overrides = []): Shop
{
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);

    return Shop::create(array_merge([
        'owner_id' => $owner->id,
        'shop_name' => 'Delivery Laundry',
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

function dlvCustomer(): User
{
    return User::factory()->create(['role' => AccountType::Customer->value]);
}

function dlvOrder(Shop $shop, User $customer, array $overrides = []): ShopOrder
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
        'customer_name' => $customer->name,
        'customer_phone' => '09181234567',
        'pickup_type' => 'pickup',
        'pricing_model' => 'per_kg',
        'price_per_kg' => 60,
        'estimated_weight_kg' => 3,
        'actual_weight_kg' => 3,
        'additional_charges' => 20,
        'discount_amount' => 10,
        'total_amount' => 190,
        'amount_paid' => 190,
        'payment_status' => 'paid',
        'paid_at' => now(),
        'status' => 'completed',
        'completed_at' => now(),
    ], $overrides));
}

/** OSRM answers with a fixed road distance in meters. */
function fakeRoadDistance(float $meters): void
{
    config()->set('services.osrm.url', 'https://osrm.test');
    Http::fake(['osrm.test/*' => Http::response(['code' => 'Ok', 'routes' => [['distance' => $meters]]])]);
}

function deliveryPayload(array $overrides = []): array
{
    return array_merge([
        'delivery_address' => 'Blk 1 Lot 2, Anabu, Imus, Cavite',
        'destination_lat' => 14.4012,
        'destination_lng' => 120.9401,
    ], $overrides);
}

// ─── Request delivery ─────────────────────────────────────────────────────────

test('a customer requests delivery and the fee is added to the order balance', function () {
    Notification::fake();
    fakeRoadDistance(4500); // 4.5 km -> 30 + (4.5 - 1) * 10 = 65
    $shop = dlvShop();
    $customer = dlvCustomer();
    $order = dlvOrder($shop, $customer);

    $this->actingAs($customer)
        ->from(route('user.orders.show', $order->payment_public_id))
        ->post(route('user.orders.request-delivery', $order->payment_public_id), deliveryPayload())
        ->assertRedirect(route('user.orders.show', $order->payment_public_id))
        ->assertSessionHas('toast', ['type' => 'success', 'message' => 'Delivery requested! The shop will assign a rider shortly.']);

    $delivery = Delivery::where('shop_order_id', $order->id)->firstOrFail();
    expect((float) $delivery->delivery_fee)->toBe(65.0)
        ->and((float) $delivery->distance_km)->toBe(4.5)
        ->and($delivery->destination_lat)->toBe(14.4012)
        ->and($delivery->destination_lng)->toBe(120.9401)
        ->and($delivery->status)->toBe('pending')
        ->and($delivery->public_id)->toHaveLength(26);

    $order->refresh();
    expect((float) $order->delivery_fee)->toBe(65.0)
        ->and((float) $order->total_amount)->toBe(255.0)
        ->and((float) $order->amount_paid)->toBe(190.0)
        ->and($order->payment_status)->toBe('partial')
        ->and($order->balanceDue())->toBe(65.0)
        ->and($order->delivery_address)->toBe('Blk 1 Lot 2, Anabu, Imus, Cavite');

    Notification::assertSentTo($shop->owner, DeliveryNotification::class);
});

test('a free delivery keeps a paid order fully paid', function () {
    Notification::fake();
    fakeRoadDistance(2000);
    $shop = dlvShop(['delivery_fee_mode' => 'fixed', 'delivery_fixed_fee' => 0]);
    $customer = dlvCustomer();
    $order = dlvOrder($shop, $customer);

    $this->actingAs($customer)
        ->post(route('user.orders.request-delivery', $order->payment_public_id), deliveryPayload())
        ->assertSessionHas('toast.type', 'success');

    expect($order->fresh()->payment_status)->toBe('paid');
});

test('a delivery request requires an address and a pinned location', function () {
    $shop = dlvShop();
    $customer = dlvCustomer();
    $order = dlvOrder($shop, $customer);

    $this->actingAs($customer)
        ->post(route('user.orders.request-delivery', $order->payment_public_id), [
            'delivery_address' => '',
            'destination_lat' => 123,
        ])
        ->assertSessionHasErrors(['delivery_address', 'destination_lat', 'destination_lng']);

    expect(Delivery::count())->toBe(0);
});

test('a second delivery request for the same order is rejected', function () {
    Notification::fake();
    fakeRoadDistance(3000);
    $shop = dlvShop();
    $customer = dlvCustomer();
    $order = dlvOrder($shop, $customer);

    $this->actingAs($customer)->post(route('user.orders.request-delivery', $order->payment_public_id), deliveryPayload());

    // The first request turned the order partial, so it is no longer eligible either way;
    // force it back to paid to prove the duplicate check itself.
    $order->refresh()->update(['payment_status' => 'paid']);

    $this->actingAs($customer)
        ->post(route('user.orders.request-delivery', $order->payment_public_id), deliveryPayload())
        ->assertSessionHas('toast', [
            'type' => 'error',
            'message' => 'A delivery has already been requested for this order.',
        ]);

    expect(Delivery::where('shop_order_id', $order->id)->count())->toBe(1);
});

test('delivery cannot be requested for an unpaid or unfinished order', function (array $state) {
    fakeRoadDistance(3000);
    $shop = dlvShop();
    $customer = dlvCustomer();
    $order = dlvOrder($shop, $customer, $state);

    $this->actingAs($customer)
        ->post(route('user.orders.request-delivery', $order->payment_public_id), deliveryPayload())
        ->assertSessionHas('toast.type', 'error');

    expect(Delivery::count())->toBe(0)
        ->and((float) $order->fresh()->total_amount)->toBe((float) $order->total_amount);
})->with([
    'unpaid' => [['payment_status' => 'unpaid', 'amount_paid' => 0, 'paid_at' => null]],
    'partial' => [['payment_status' => 'partial', 'amount_paid' => 100, 'paid_at' => null]],
    'in progress' => [['status' => 'in_progress', 'completed_at' => null]],
]);

test('a customer cannot request delivery for another customers order', function () {
    fakeRoadDistance(3000);
    $shop = dlvShop();
    $order = dlvOrder($shop, dlvCustomer());

    $this->actingAs(dlvCustomer())
        ->post(route('user.orders.request-delivery', $order->payment_public_id), deliveryPayload())
        ->assertNotFound();

    expect(Delivery::count())->toBe(0);
});

test('delivery requests are blocked when the shop does not offer delivery', function () {
    $shop = dlvShop(['offers_delivery' => false]);
    $customer = dlvCustomer();
    $order = dlvOrder($shop, $customer);

    $this->actingAs($customer)
        ->post(route('user.orders.request-delivery', $order->payment_public_id), deliveryPayload())
        ->assertForbidden();

    expect(Delivery::count())->toBe(0);
});

test('a destination beyond the delivery radius is rejected with a toast', function () {
    fakeRoadDistance(12_500);
    $shop = dlvShop();
    $customer = dlvCustomer();
    $order = dlvOrder($shop, $customer);

    $this->actingAs($customer)
        ->post(route('user.orders.request-delivery', $order->payment_public_id), deliveryPayload())
        ->assertSessionHas('toast.type', 'error');

    expect(Delivery::count())->toBe(0)
        ->and($order->fresh()->payment_status)->toBe('paid');
});

// ─── Quote ────────────────────────────────────────────────────────────────────

test('a customer gets a delivery quote for a pinned location', function () {
    fakeRoadDistance(4500);
    $shop = dlvShop();
    $customer = dlvCustomer();
    $order = dlvOrder($shop, $customer);

    $this->actingAs($customer)
        ->postJson(route('user.orders.delivery-quote', $order->payment_public_id), [
            'destination_lat' => 14.4012,
            'destination_lng' => 120.9401,
        ])
        ->assertOk()
        ->assertJson(['distance_km' => 4.5, 'fee' => 65, 'available' => true]);
});

test('a quote beyond the delivery radius is unavailable with a message', function () {
    fakeRoadDistance(15_000);
    $shop = dlvShop();
    $customer = dlvCustomer();
    $order = dlvOrder($shop, $customer);

    $this->actingAs($customer)
        ->postJson(route('user.orders.delivery-quote', $order->payment_public_id), [
            'destination_lat' => 14.30,
            'destination_lng' => 120.90,
        ])
        ->assertOk()
        ->assertJson(['distance_km' => 15, 'fee' => null, 'available' => false])
        ->assertJsonPath('message', fn (string $message) => str_contains($message, 'within 10 km'));
});

test('a quote is unavailable when the shop has no location', function () {
    Http::fake();
    $shop = dlvShop(['latitude' => null, 'longitude' => null]);
    $customer = dlvCustomer();
    $order = dlvOrder($shop, $customer);

    $this->actingAs($customer)
        ->postJson(route('user.orders.delivery-quote', $order->payment_public_id), [
            'destination_lat' => 14.30,
            'destination_lng' => 120.90,
        ])
        ->assertOk()
        ->assertJson(['available' => false, 'distance_km' => null]);

    Http::assertNothingSent();
});

test('quotes require authentication, ownership and valid coordinates', function () {
    fakeRoadDistance(1000);
    $shop = dlvShop();
    $customer = dlvCustomer();
    $order = dlvOrder($shop, $customer);
    $url = route('user.orders.delivery-quote', $order->payment_public_id);
    $coords = ['destination_lat' => 14.4, 'destination_lng' => 120.9];

    $this->postJson($url, $coords)->assertUnauthorized();
    $this->actingAs(dlvCustomer())->postJson($url, $coords)->assertNotFound();
    $this->actingAs($customer)->postJson($url, ['destination_lat' => 95])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['destination_lat', 'destination_lng']);
});

// ─── Tracking ─────────────────────────────────────────────────────────────────

function dlvDelivery(ShopOrder $order, array $overrides = []): Delivery
{
    return Delivery::create(array_merge([
        'shop_id' => $order->shop_id,
        'shop_order_id' => $order->id,
        'customer_name' => $order->customer_name,
        'delivery_address' => 'Anabu, Imus',
        'destination_lat' => 14.4012,
        'destination_lng' => 120.9401,
        'distance_km' => 4.5,
        'delivery_fee' => 65,
        'status' => 'pending',
    ], $overrides));
}

test('the order owner can poll the tracking payload', function () {
    $shop = dlvShop();
    $customer = dlvCustomer();
    $order = dlvOrder($shop, $customer);
    $rider = Rider::create(['shop_id' => $shop->id, 'name' => 'Juan Rider', 'status' => 'active']);
    dlvDelivery($order, [
        'rider_id' => $rider->id,
        'status' => 'picked_up',
        'assigned_at' => now()->subMinutes(20),
        'picked_up_at' => now()->subMinutes(5),
        'rider_lat' => 14.415,
        'rider_lng' => 120.938,
        'rider_located_at' => now(),
    ]);

    $this->actingAs($customer)
        ->getJson(route('user.orders.tracking', $order->payment_public_id))
        ->assertOk()
        ->assertJsonStructure([
            'public_id', 'status', 'is_active', 'delivery_address', 'distance_km', 'delivery_fee', 'rider_name',
            'shop' => ['name', 'lat', 'lng'],
            'destination' => ['lat', 'lng'],
            'rider' => ['lat', 'lng', 'located_at'],
            'timeline' => ['requested_at', 'assigned_at', 'picked_up_at', 'delivered_at', 'failed_at'],
        ])
        ->assertJsonMissingPath('id')
        ->assertJsonMissingPath('driver_token')
        ->assertJson([
            'status' => 'picked_up',
            'is_active' => true,
            'rider_name' => 'Juan Rider',
            'shop' => ['lat' => 14.4296, 'lng' => 120.9367],
            'rider' => ['lat' => 14.415, 'lng' => 120.938],
            'delivery_fee' => 65,
        ]);
});

test('rider coordinates are hidden once the delivery is finished', function () {
    $shop = dlvShop();
    $customer = dlvCustomer();
    $order = dlvOrder($shop, $customer);
    dlvDelivery($order, [
        'status' => 'delivered',
        'delivered_at' => now(),
        'rider_lat' => 14.415,
        'rider_lng' => 120.938,
    ]);

    $this->actingAs($customer)
        ->getJson(route('user.orders.tracking', $order->payment_public_id))
        ->assertOk()
        ->assertJson(['is_active' => false, 'rider' => null]);
});

test('another customer cannot track the delivery', function () {
    $shop = dlvShop();
    $order = dlvOrder($shop, dlvCustomer());
    dlvDelivery($order);

    $this->actingAs(dlvCustomer())
        ->getJson(route('user.orders.tracking', $order->payment_public_id))
        ->assertNotFound();
});

test('tracking an order without a delivery is not found', function () {
    $customer = dlvCustomer();
    $order = dlvOrder(dlvShop(), $customer);

    $this->actingAs($customer)
        ->getJson(route('user.orders.tracking', $order->payment_public_id))
        ->assertNotFound();
});

test('the order page exposes delivery fee and map coordinates without sequential ids', function () {
    $shop = dlvShop();
    $customer = dlvCustomer();
    $order = dlvOrder($shop, $customer, [
        'delivery_fee' => 65,
        'total_amount' => 255,
        'payment_status' => 'partial',
    ]);
    $delivery = dlvDelivery($order);

    $this->actingAs($customer)
        ->get(route('user.orders.show', $order->payment_public_id))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('order.delivery.public_id', $delivery->public_id)
            ->missing('order.delivery.id')
            ->where('order.delivery.delivery_fee', 65)
            ->where('order.delivery.destination.lat', 14.4012)
            ->where('order.shop.latitude', 14.4296)
            ->where('order.billing.delivery_fee', 65)
            ->where('order.billing.total', 255)
            ->where('order.billing.balance_due', 65));
});
