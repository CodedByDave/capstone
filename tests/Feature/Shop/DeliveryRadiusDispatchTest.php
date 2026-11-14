<?php

use App\Enums\AccountType;
use App\Models\Delivery;
use App\Models\Shop;
use App\Models\ShopOrder;
use App\Models\ShopService;
use App\Models\User;
use App\Notifications\DeliveryHeldNotification;
use App\Services\DeliveryRadiusService;
use App\Services\DistanceService;
use App\Services\LogisticsService;
use App\Services\MapboxService;
use Illuminate\Support\Facades\Notification;

/** Straight-line distance and no network: the tests never reach Mapbox. */
beforeEach(function () {
    $this->app->instance(DistanceService::class, new class extends DistanceService
    {
        public function __construct() {}

        public function between(float $fromLat, float $fromLng, float $toLat, float $toLng): array
        {
            return ['distance_km' => round($this->haversineKm($fromLat, $fromLng, $toLat, $toLng), 2), 'source' => 'haversine'];
        }
    });
    $this->app->instance(MapboxService::class, new class extends MapboxService
    {
        public function __construct() {}

        public function geocode(string $query, ?float $nearLat = null, ?float $nearLng = null): array
        {
            return [];
        }
    });
});

function radiusShop(?float $maxKm = 5): Shop
{
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);

    return Shop::create([
        'owner_id' => $owner->id,
        'shop_name' => 'Radius Laundry',
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Poblacion',
        'postal_code' => '4103',
        'status' => 'active',
        'setup_completed_at' => now(),
        'is_open_24_7' => true,
        'offers_delivery' => true,
        'delivery_pricing_set_at' => now(),
        'latitude' => 14.4296,
        'longitude' => 120.9367,
        'delivery_max_km' => $maxKm,
    ]);
}

function radiusOrder(Shop $shop, ?float $lat, ?float $lng): ShopOrder
{
    $service = ShopService::create([
        'shop_id' => $shop->id, 'service_name' => 'Wash and Fold', 'is_active' => true,
        'pricing_model' => 'per_kg', 'price_per_kg' => 60,
    ]);

    return ShopOrder::create([
        'shop_id' => $shop->id,
        'order_source' => 'shop',
        'service_id' => $service->id,
        'order_number' => 'ORD-'.strtoupper(uniqid()),
        'customer_name' => 'Customer',
        'customer_phone' => '09181234567',
        'customer_address' => 'Somewhere',
        'customer_lat' => $lat,
        'customer_lng' => $lng,
        'deliver_to_customer' => true,
        'pickup_type' => 'walk_in',
        'pricing_model' => 'per_kg',
        'price_per_kg' => 60,
        'actual_weight_kg' => 5,
        'total_amount' => 300,
        'payment_status' => 'unpaid',
        'status' => 'completed',
    ]);
}

test('the radius check holds a point beyond the limit and passes one inside it', function () {
    $shop = radiusShop(5);
    $radius = app(DeliveryRadiusService::class);

    expect($radius->heldReason($shop, 14.4300, 120.9370))->toBeNull()
        ->and($radius->heldReason($shop, 14.6000, 121.1000))->toContain('Outside delivery radius')
        ->and($radius->heldReason($shop, null, null))->toContain('Could not verify');
});

test('a shop without a radius never holds a delivery', function () {
    expect(app(DeliveryRadiusService::class)->heldReason(radiusShop(null), 14.9, 121.5))->toBeNull();
});

test('a delivery beyond the radius is held with no rider and the shop is told', function () {
    Notification::fake();
    $shop = radiusShop(5);

    $delivery = app(LogisticsService::class)->dispatchCompletedOrder(radiusOrder($shop, 14.6000, 121.1000));

    expect($delivery)->toBeInstanceOf(Delivery::class)
        ->and($delivery->rider_id)->toBeNull()
        ->and($delivery->status)->toBe('pending')
        ->and($delivery->notes)->toContain('Outside delivery radius');
    Notification::assertSentTo($shop->owner, DeliveryHeldNotification::class);
});

test('staff sending a far address for delivery is held, not refused', function () {
    Notification::fake();
    $shop = radiusShop(5);

    $delivery = app(LogisticsService::class)->sendOrderForDelivery($shop->owner, radiusOrder($shop, 14.6000, 121.1000), null);

    expect($delivery->rider_id)->toBeNull()
        ->and($delivery->notes)->toContain('Outside delivery radius');
});

test('an address that cannot be located is held as unverified', function () {
    Notification::fake();
    $shop = radiusShop(5);

    $delivery = app(LogisticsService::class)->dispatchCompletedOrder(radiusOrder($shop, null, null));

    expect($delivery->notes)->toContain('Could not verify the distance');
    Notification::assertSentTo($shop->owner, DeliveryHeldNotification::class);
});

test('an order inside the radius is not held for distance', function () {
    Notification::fake();
    $shop = radiusShop(5);

    $delivery = app(LogisticsService::class)->dispatchCompletedOrder(radiusOrder($shop, 14.4300, 120.9400));

    expect($delivery->notes)->not->toContain('Outside delivery radius');
    Notification::assertNotSentTo($shop->owner, DeliveryHeldNotification::class);
});

test('the settings page lists recent customer distances for the radius warning', function () {
    $shop = radiusShop(5);
    radiusOrder($shop, 14.4300, 120.9400);
    $this->withoutMiddleware([
        \App\Http\Middleware\EnsureCurrentBusinessAgreementAccepted::class,
        \App\Http\Middleware\EnforceOwnerPlatformPermissions::class,
    ]);

    $this->actingAs($shop->owner)->get(route('shop.settings'))
        ->assertInertia(fn ($page) => $page->has('deliveryPricing.recent_distances_km', 1));
});
