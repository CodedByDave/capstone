<?php

use App\Enums\AccountType;
use App\Http\Middleware\EnforceOwnerPlatformPermissions;
use App\Http\Middleware\EnsureCurrentBusinessAgreementAccepted;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\Rider;
use App\Models\Shop;
use App\Models\ShopOrder;
use App\Models\ShopService;
use App\Models\User;
use App\Services\CustomerAgreementService;
use App\Services\LogisticsService;
use App\Services\RiderLoadLimitService;
use App\Services\ShopOrderService;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->withoutMiddleware([
        EnsureCurrentBusinessAgreementAccepted::class,
        EnforceOwnerPlatformPermissions::class,
    ]);
});

/** A shop on a paid plan that delivers, with a 3 to 15 kg rider load limit unless told otherwise. */
function loadShop(?float $min = 3, ?float $max = 15, string $name = 'Load Laundry'): Shop
{
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
    $shop = Shop::create([
        'owner_id' => $owner->id,
        'shop_name' => $name,
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Poblacion',
        'postal_code' => '4103',
        'status' => 'active',
        'setup_completed_at' => now(),
        'is_open_24_7' => true,
        'offers_pickup' => true,
        'offers_delivery' => true,
        'delivery_pricing_set_at' => now(),
        'rider_vehicle_limits' => ['Motorcycle' => ['min' => $min, 'max' => $max]],
    ]);
    Order::create([
        'user_id' => $owner->id,
        'shop_name' => $name,
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

    return $shop;
}

/** Pickup and delivery are only offered while a clocked-in rider is online. */
function loadEnsureRider(Shop $shop): void
{
    if (! Rider::where('shop_id', $shop->id)->exists()) {
        onlineRider($shop);
    }
}

function loadService(Shop $shop, string $model = 'per_kg'): ShopService
{
    return ShopService::firstOrCreate(
        ['shop_id' => $shop->id, 'service_name' => $model === 'per_piece' ? 'Comforter' : 'Wash and Fold'],
        $model === 'per_piece'
            ? ['is_active' => true, 'pricing_model' => 'per_piece', 'price_per_piece' => 150]
            : ['is_active' => true, 'pricing_model' => 'per_kg', 'price_per_kg' => 60],
    );
}

/** A customer booking a rider pickup with an estimated weight. */
function loadCustomerPickup(Shop $shop, ?float $weight, string $model = 'per_kg'): ShopOrder
{
    loadEnsureRider($shop);
    $customer = User::factory()->create(['role' => AccountType::Customer->value, 'referred_shop_id' => $shop->id]);

    return app(ShopOrderService::class)->createCustomerOrder($customer, [
        'shop_id' => $shop->id,
        'service_id' => loadService($shop, $model)->id,
        'customer_name' => 'Juan',
        'customer_phone' => '09171234567',
        'estimated_weight_kg' => $weight,
        'piece_count' => $model === 'per_piece' ? 2 : null,
        'pickup_type' => 'pickup',
        'customer_address' => 'Anabu, Imus',
        'alternate_contact_name' => 'Maria',
        'alternate_contact_phone' => '09181234567',
        'customer_agreement_version' => app(CustomerAgreementService::class)->agreementData($shop)['version'],
    ], null, null);
}

/** Staff creating a walk-in order that the shop delivers back, weighed at the counter. */
function loadStaffDelivery(Shop $shop, float $weight): ShopOrder
{
    loadEnsureRider($shop);
    $service = loadService($shop);

    return app(ShopOrderService::class)->createOrder([
        'shop_id' => $shop->id,
        'customer_name' => 'Walk-in Ana',
        'customer_phone' => '09171234567',
        'customer_address' => 'Anabu, Imus',
        'alternate_contact_name' => 'Maria',
        'alternate_contact_phone' => '09181234567',
        'deliver_to_customer' => true,
        'service_id' => $service->id,
        'pickup_type' => 'walk_in',
        'pricing_model' => 'per_kg',
        'price_per_kg' => 60,
        'actual_weight_kg' => $weight,
        'total_amount' => round($weight * 60, 2),
        'amount_paid' => 0,
        'customer_agreement_version' => app(CustomerAgreementService::class)->agreementDataForShopId($shop->id)['version'],
        'customer_agreement_accepted' => true,
    ], $shop->owner);
}

/** A finished order weighed at the shop, ready to be delivered back. */
function loadCompletedOrder(Shop $shop, float $weight, bool $deliver = true): ShopOrder
{
    return ShopOrder::create([
        'shop_id' => $shop->id,
        'order_source' => 'shop',
        'service_id' => loadService($shop)->id,
        'order_number' => 'ORD-'.strtoupper(uniqid()),
        'customer_name' => 'Customer',
        'customer_phone' => '09181234567',
        'customer_address' => 'Anabu, Imus',
        'deliver_to_customer' => $deliver,
        'pickup_type' => 'walk_in',
        'pricing_model' => 'per_kg',
        'price_per_kg' => 60,
        'actual_weight_kg' => $weight,
        'total_amount' => $weight * 60,
        'payment_status' => 'unpaid',
        'status' => 'completed',
    ]);
}

function loadErrors(Closure $action): array
{
    try {
        $action();
    } catch (ValidationException $e) {
        return $e->errors();
    }

    return [];
}

// ─── Settings ──────────────────────────────────────────────────────────────────

test('the owner sets the load limit of each vehicle type', function () {
    $shop = loadShop(null, null);

    $this->actingAs($shop->owner)
        ->patch('/shop/settings/rider-load', [
            'rider_vehicle_types' => ['Motorcycle', 'Car'],
            'rider_vehicle_limits' => [
                ['type' => 'Motorcycle', 'min' => '2.5', 'max' => '20'],
                ['type' => 'Car', 'min' => '', 'max' => '40'],
            ],
        ])
        ->assertSessionHasNoErrors();

    expect($shop->fresh()->rider_vehicle_limits)->toBe([
        'Motorcycle' => ['min' => 2.5, 'max' => 20],
        'Car' => ['min' => null, 'max' => 40],
    ]);
});

test('empty fields remove the limit', function () {
    $shop = loadShop();

    $this->actingAs($shop->owner)
        ->patch('/shop/settings/rider-load', [
            'rider_vehicle_types' => ['Motorcycle'],
            'rider_vehicle_limits' => [['type' => 'Motorcycle', 'min' => '', 'max' => '']],
        ])
        ->assertSessionHasNoErrors();

    expect($shop->fresh()->rider_vehicle_limits)->toBe(['Motorcycle' => ['min' => null, 'max' => null]]);
});

test('the rider load limit must be sensible', function (array $limit, string $field) {
    $shop = loadShop(3, 15);

    $this->actingAs($shop->owner)
        ->patch('/shop/settings/rider-load', [
            'rider_vehicle_types' => ['Motorcycle'],
            'rider_vehicle_limits' => [['type' => 'Motorcycle'] + $limit],
        ])
        ->assertSessionHasErrors($field);

    expect($shop->fresh()->rider_vehicle_limits)->toBe(['Motorcycle' => ['min' => 3, 'max' => 15]]);
})->with([
    'max below min' => [['min' => '10', 'max' => '5'], 'rider_vehicle_limits.0.max'],
    'zero minimum' => [['min' => '0', 'max' => '5'], 'rider_vehicle_limits.0.min'],
    'too heavy' => [['min' => '', 'max' => '500'], 'rider_vehicle_limits.0.max'],
    'not a number' => [['min' => 'abc', 'max' => ''], 'rider_vehicle_limits.0.min'],
]);

test('staff cannot change the rider load limit', function () {
    $shop = loadShop(3, 15);
    $staff = User::factory()->create(['role' => AccountType::Staff->value]);

    $this->actingAs($staff)->patch('/shop/settings/rider-load', [
        'rider_vehicle_types' => ['Motorcycle'],
        'rider_vehicle_limits' => [['type' => 'Motorcycle', 'min' => '', 'max' => '5']],
    ]);

    expect($shop->fresh()->rider_vehicle_limits)->toBe(['Motorcycle' => ['min' => 3, 'max' => 15]]);
});

test('each owner only changes their own shop', function () {
    $mine = loadShop(null, null, 'Mine');
    $other = loadShop(3, 15, 'Other');

    $this->actingAs($mine->owner)
        ->patch('/shop/settings/rider-load', [
            'rider_vehicle_types' => ['Motorcycle'],
            'rider_vehicle_limits' => [['type' => 'Motorcycle', 'min' => '', 'max' => '9']],
        ])
        ->assertSessionHasNoErrors();

    expect($mine->fresh()->rider_vehicle_limits['Motorcycle']['max'])->toBe(9)
        ->and($other->fresh()->rider_vehicle_limits['Motorcycle']['max'])->toBe(15);
});

test('saving settings never changes a rider vehicle', function () {
    $shop = loadShop(3, 15);
    $car = onlineRider($shop, 'Carl');
    $car->update(['vehicle_type' => 'Car']);

    $this->actingAs($shop->owner)
        ->patch('/shop/settings/rider-load', [
            'rider_vehicle_types' => ['Motorcycle', 'Car'],
            'rider_vehicle_limits' => [
                ['type' => 'Motorcycle', 'min' => '3', 'max' => '15'],
                ['type' => 'Car', 'min' => '', 'max' => '40'],
            ],
        ])
        ->assertSessionHasNoErrors();

    expect($car->fresh()->vehicle_type)->toBe('Car');
});

test('the settings page lists the capacity of each vehicle type and no vehicle in use', function () {
    $shop = loadShop(3, 15);
    $shop->update([
        'rider_vehicle_types' => ['Motorcycle', 'Car'],
        'rider_vehicle_limits' => ['Motorcycle' => ['min' => 3, 'max' => 15], 'Car' => ['min' => 5, 'max' => 40]],
    ]);

    $this->actingAs($shop->owner)->get('/shop/settings')
        ->assertInertia(fn ($page) => $page
            ->where('riderVehicle.choices', ['Motorcycle', 'Car'])
            ->where('riderVehicle.limits.0', ['type' => 'Motorcycle', 'min' => 3, 'max' => 15])
            ->where('riderVehicle.limits.1', ['type' => 'Car', 'min' => 5, 'max' => 40])
            ->missing('riderVehicle.selected'));
});

test('a shop starts with motorcycle as its only vehicle type', function () {
    $shop = loadShop();

    $this->actingAs($shop->owner)->get('/shop/settings')
        ->assertInertia(fn ($page) => $page->where('riderVehicle.choices', ['Motorcycle']));
});

test('motorcycle always stays a choice and repeats are dropped', function () {
    $shop = loadShop();

    $this->actingAs($shop->owner)
        ->patch('/shop/settings/rider-load', ['rider_vehicle_types' => ['  Van ', 'van', '']])
        ->assertSessionHasNoErrors();

    expect($shop->fresh()->rider_vehicle_types)->toBe(['Motorcycle', 'Van']);
});

test('the vehicle types must be valid', function (array $input, string $field) {
    $shop = loadShop();

    $this->actingAs($shop->owner)
        ->patch('/shop/settings/rider-load', $input)
        ->assertSessionHasErrors($field);
})->with([
    'too long' => [['rider_vehicle_types' => [str_repeat('a', 41)]], 'rider_vehicle_types.1'],
    'too many' => [['rider_vehicle_types' => ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j']], 'rider_vehicle_types'],
    'list missing' => [[], 'rider_vehicle_types'],
]);

test('a removed vehicle type loses its capacity', function () {
    $shop = loadShop(3, 15);
    $shop->update([
        'rider_vehicle_types' => ['Motorcycle', 'Car'],
        'rider_vehicle_limits' => ['Motorcycle' => ['min' => 3, 'max' => 15], 'Car' => ['min' => 5, 'max' => 40]],
    ]);

    $this->actingAs($shop->owner)
        ->patch('/shop/settings/rider-load', [
            'rider_vehicle_types' => ['Motorcycle'],
            'rider_vehicle_limits' => [['type' => 'Motorcycle', 'min' => '3', 'max' => '15']],
        ])
        ->assertSessionHasNoErrors();

    expect($shop->fresh()->rider_vehicle_limits)->toBe(['Motorcycle' => ['min' => 3, 'max' => 15]]);
});

// ─── Customer pickup booking ───────────────────────────────────────────────────

test('a pickup at exactly the limits is accepted', function (float $weight) {
    $shop = loadShop(3, 15);

    $order = loadCustomerPickup($shop, $weight);

    expect($order->pickup_type)->toBe('pickup');
})->with(['maximum' => 15, 'minimum' => 3, 'in between' => 8]);

test('a pickup above the maximum is refused with a reason', function () {
    $shop = loadShop(3, 15);

    $errors = loadErrors(fn () => loadCustomerPickup($shop, 15.5));

    expect($errors['pickup_type'][0] ?? '')->toContain('up to 15 kg')
        ->toContain('drop it off at the shop')
        ->and(ShopOrder::where('shop_id', $shop->id)->count())->toBe(0);
});

test('a pickup below the minimum is refused with a reason', function () {
    $shop = loadShop(3, 15);

    $errors = loadErrors(fn () => loadCustomerPickup($shop, 2));

    expect($errors['pickup_type'][0] ?? '')->toContain('at least 3 kg')
        ->and(ShopOrder::where('shop_id', $shop->id)->count())->toBe(0);
});

test('heavy laundry can still be booked as a drop-off', function () {
    $shop = loadShop(3, 15);
    $customer = User::factory()->create(['role' => AccountType::Customer->value, 'referred_shop_id' => $shop->id]);

    $order = app(ShopOrderService::class)->createCustomerOrder($customer, [
        'shop_id' => $shop->id,
        'service_id' => loadService($shop)->id,
        'customer_name' => 'Juan',
        'customer_phone' => '09171234567',
        'estimated_weight_kg' => 40,
        'pickup_type' => 'walk_in',
        'customer_agreement_version' => app(CustomerAgreementService::class)->agreementData($shop)['version'],
    ], null, null);

    expect($order->pickup_type)->toBe('walk_in');
});

test('a shop without a limit takes any pickup weight', function () {
    $shop = loadShop(null, null);

    expect(loadCustomerPickup($shop, 60)->pickup_type)->toBe('pickup');
});

test('per-piece laundry has no weight and is never refused', function () {
    $shop = loadShop(3, 15);

    expect(loadCustomerPickup($shop, null, 'per_piece')->pickup_type)->toBe('pickup');
});

test('the booking page tells the customer the limit', function () {
    $shop = loadShop(3, 15);
    $customer = User::factory()->create(['role' => AccountType::Customer->value, 'referred_shop_id' => $shop->id]);

    $this->actingAs($customer)->get(route('user.shops.show', $shop))
        ->assertInertia(fn ($page) => $page
            ->where('shop.rider_load_limit.min', 3)
            ->where('shop.rider_load_limit.max', 15));
});

// ─── Staff orders with a delivery ──────────────────────────────────────────────

test('staff cannot create a delivery order heavier than a rider can carry', function () {
    $shop = loadShop(3, 15);

    $errors = loadErrors(fn () => loadStaffDelivery($shop, 18));

    expect($errors['deliver_to_customer'][0] ?? '')->toContain('up to 15 kg')
        ->and(ShopOrder::where('shop_id', $shop->id)->count())->toBe(0);
});

test('staff can create a delivery order within the limit', function () {
    $shop = loadShop(3, 15);

    expect(loadStaffDelivery($shop, 15)->deliver_to_customer)->toBeTrue();
});

test('the new order form receives the limit', function () {
    $shop = loadShop(3, 15);

    $this->actingAs($shop->owner)->get('/shop/operations/orders/create')
        ->assertInertia(fn ($page) => $page
            ->where('riderLoadLimit.min', 3)
            ->where('riderLoadLimit.max', 15));
});

// ─── Send for delivery ─────────────────────────────────────────────────────────

test('an order heavier than the limit cannot be sent for delivery', function () {
    $shop = loadShop(3, 15);
    $order = loadCompletedOrder($shop, 20, deliver: false);

    $errors = loadErrors(fn () => app(LogisticsService::class)->sendOrderForDelivery($shop->owner, $order, 'Anabu, Imus'));

    expect($errors['delivery'][0] ?? '')->toContain('up to 15 kg')
        ->and(Delivery::where('shop_order_id', $order->id)->exists())->toBeFalse();
});

test('an order within the limit is sent for delivery', function () {
    $shop = loadShop(3, 15);
    $order = loadCompletedOrder($shop, 12, deliver: false);

    $delivery = app(LogisticsService::class)->sendOrderForDelivery($shop->owner, $order, 'Anabu, Imus');

    expect($delivery->type)->toBe(Delivery::TYPE_DELIVERY);
});

// ─── Delivery after weighing ───────────────────────────────────────────────────

test('an overweight delivery is held with no rider for staff to decide', function () {
    $shop = loadShop(3, 15);
    onlineRider($shop, 'Rico');
    $order = loadCompletedOrder($shop, 18);

    $delivery = app(LogisticsService::class)->dispatchCompletedOrder($order);

    expect($delivery)->not->toBeNull()
        ->and($delivery->rider_id)->toBeNull()
        ->and($delivery->status)->toBe('pending')
        ->and($delivery->notes)->toContain('Over rider limit (18 kg / max 15 kg)');
});

test('a delivery within the limit goes to a free rider as before', function () {
    $shop = loadShop(3, 15);
    $rider = onlineRider($shop, 'Rico');
    $order = loadCompletedOrder($shop, 15);

    $delivery = app(LogisticsService::class)->dispatchCompletedOrder($order);

    expect($delivery->rider_id)->toBe($rider->id)
        ->and($delivery->status)->toBe('assigned');
});

test('auto-assign skips a held delivery but gives the next one to the rider', function () {
    $shop = loadShop(3, 15);
    $heavy = app(LogisticsService::class)->dispatchCompletedOrder(loadCompletedOrder($shop, 18));
    $light = app(LogisticsService::class)->dispatchCompletedOrder(loadCompletedOrder($shop, 6));
    expect($light->rider_id)->toBeNull();

    $rider = onlineRider($shop, 'Rico');
    $assigned = app(LogisticsService::class)->assignWaitingDeliveries($shop->id);

    expect($assigned)->toBe(1)
        ->and($heavy->fresh()->rider_id)->toBeNull()
        ->and($light->fresh()->rider_id)->toBe($rider->id);
});

test('staff can still hand a held delivery to a rider themselves', function () {
    $shop = loadShop(3, 15);
    $delivery = app(LogisticsService::class)->dispatchCompletedOrder(loadCompletedOrder($shop, 18));
    $rider = onlineRider($shop, 'Rico');

    app(LogisticsService::class)->updateStatus($shop->owner, $delivery, ['status' => 'assigned', 'rider_id' => $rider->id]);

    expect($delivery->fresh()->rider_id)->toBe($rider->id);
});

test('the logistics board flags a held delivery', function () {
    $shop = loadShop(3, 15);
    app(LogisticsService::class)->dispatchCompletedOrder(loadCompletedOrder($shop, 18));
    app(LogisticsService::class)->dispatchCompletedOrder(loadCompletedOrder($shop, 6));

    $board = app(LogisticsService::class)->board($shop->owner, null);
    $warnings = collect($board['deliveries']->items())->pluck('load_warning')->filter()->values()->all();

    expect($warnings)->toBe(['Over rider limit (18 kg / max 15 kg)']);
});

test('without a limit nothing is held', function () {
    $shop = loadShop(null, null);
    $rider = onlineRider($shop, 'Rico');

    $delivery = app(LogisticsService::class)->dispatchCompletedOrder(loadCompletedOrder($shop, 40));

    expect($delivery->rider_id)->toBe($rider->id);
});

// ─── Weight of an order ────────────────────────────────────────────────────────

test('an order weighs its main service plus its weighed extra services', function () {
    $service = app(RiderLoadLimitService::class);

    expect($service->weightFromParts('per_kg', 10, 4, [
        ['pricing_model' => 'per_kg', 'weight_kg' => 3],
        ['pricing_model' => 'per_piece', 'weight_kg' => null],
    ]))->toBe(13.0)
        ->and($service->weightFromParts('per_kg', null, 4))->toBe(4.0)
        ->and($service->weightFromParts('per_piece', null, null))->toBeNull();
});

// ─── Customer asks for delivery of a finished order ────────────────────────────

/** A finished, paid order of the given weight that belongs to a customer, at a shop with a location. */
function loadCustomerFinishedOrder(Shop $shop, float $weight): array
{
    $shop->update(['latitude' => 14.4297, 'longitude' => 120.9367]);
    $customer = User::factory()->create(['role' => AccountType::Customer->value, 'referred_shop_id' => $shop->id]);
    $order = loadCompletedOrder($shop, $weight, deliver: false);
    $order->update(['user_id' => $customer->id, 'order_source' => 'online', 'payment_status' => 'paid', 'amount_paid' => $order->total_amount]);

    return [$customer, $order->fresh()];
}

test('a customer is told before any fee that heavy laundry cannot be delivered', function () {
    $shop = loadShop(3, 15);
    [$customer, $order] = loadCustomerFinishedOrder($shop, 20);

    $quote = app(\App\Services\DeliveryRequestService::class)->quote($customer, $order->payment_public_id, 14.43, 120.94);

    expect($quote['available'])->toBeFalse()
        ->and($quote['fee'])->toBeNull()
        ->and($quote['message'])->toContain('up to 15 kg')
        ->toContain('collect it at the shop');
});

test('a customer delivery request for heavy laundry is refused and nothing is charged', function () {
    $shop = loadShop(3, 15);
    [$customer, $order] = loadCustomerFinishedOrder($shop, 20);

    expect(fn () => app(\App\Services\DeliveryRequestService::class)->request($customer, $order->payment_public_id, [
        'delivery_address' => 'Anabu, Imus',
        'destination_lat' => 14.43,
        'destination_lng' => 120.94,
    ]))->toThrow(\App\Exceptions\DeliveryNotAvailableException::class);

    expect(Delivery::where('shop_order_id', $order->id)->exists())->toBeFalse()
        ->and((float) $order->fresh()->total_amount)->toBe((float) $order->total_amount);
});

test('a customer delivery request within the limit still works', function () {
    $shop = loadShop(3, 15);
    [$customer, $order] = loadCustomerFinishedOrder($shop, 10);

    $quote = app(\App\Services\DeliveryRequestService::class)->quote($customer, $order->payment_public_id, 14.43, 120.94);

    expect($quote['available'])->toBeTrue();
});

// ─── More hold cases ───────────────────────────────────────────────────────────

test('a delivery under the minimum is held too', function () {
    $shop = loadShop(3, 15);
    onlineRider($shop, 'Rico');

    $delivery = app(LogisticsService::class)->dispatchCompletedOrder(loadCompletedOrder($shop, 2));

    expect($delivery->rider_id)->toBeNull()
        ->and($delivery->notes)->toContain('Under rider minimum (2 kg / min 3 kg)');
});

test('a pickup run for an order heavier than the limit is held', function () {
    $shop = loadShop(3, 15);
    onlineRider($shop, 'Rico');
    // An order placed before the limit was lowered: its estimate is now over the maximum.
    $order = loadCompletedOrder($shop, 0);
    $order->update(['status' => 'pending', 'pickup_type' => 'pickup', 'actual_weight_kg' => null, 'estimated_weight_kg' => 20]);

    $run = app(LogisticsService::class)->dispatchPickupRun($order->fresh());

    expect($run->type)->toBe(Delivery::TYPE_PICKUP)
        ->and($run->rider_id)->toBeNull()
        ->and($run->notes)->toContain('Over rider limit');
});

test('auto-assign loads every waiting order in a fixed number of queries', function () {
    $shop = loadShop(3, 15);
    foreach ([18, 19, 20, 21, 22] as $kg) {
        app(LogisticsService::class)->dispatchCompletedOrder(loadCompletedOrder($shop, $kg));
    }
    onlineRider($shop, 'Rico');

    \Illuminate\Support\Facades\DB::enableQueryLog();
    app(LogisticsService::class)->assignWaitingDeliveries($shop->id);
    $orderQueries = collect(\Illuminate\Support\Facades\DB::getQueryLog())
        ->filter(fn ($q) => str_contains($q['query'], 'from `shop_orders`'))
        ->count();

    // One query for the orders of all five held runs, not one per run.
    expect($orderQueries)->toBe(1);
});

// ─── Fleet limit: bookings are held to what the riders can cover between them ──

/** A shop whose motorcycles carry 3 to 15 kg and whose car carries up to 40 kg. */
function loadMixedShop(): Shop
{
    $shop = loadShop(3, 15);
    $shop->update([
        'rider_vehicle_types' => ['Motorcycle', 'Car'],
        'rider_vehicle_limits' => ['Motorcycle' => ['min' => 3, 'max' => 15], 'Car' => ['min' => null, 'max' => 40]],
    ]);

    return $shop->fresh();
}

function loadCarRider(Shop $shop, string $name = 'Carl'): Rider
{
    $rider = onlineRider($shop, $name);
    $rider->update(['vehicle_type' => 'Car']);

    return $rider->fresh();
}

test('the booking limit is the widest range the active riders cover', function () {
    $shop = loadMixedShop();
    $service = app(RiderLoadLimitService::class);

    // Only a motorcycle rider: held to the motorcycle.
    onlineRider($shop, 'Rico');
    expect($service->limitsFor($shop))->toBe(['min' => 3.0, 'max' => 15.0]);

    // A car rider joins: heavier orders can be booked, and the car has no minimum.
    loadCarRider($shop);
    expect($service->limitsFor($shop))->toBe(['min' => null, 'max' => 40.0]);
});

test('a shop with no active rider is held to the default vehicle', function () {
    $shop = loadMixedShop();

    expect(app(RiderLoadLimitService::class)->limitsFor($shop))->toBe(['min' => 3.0, 'max' => 15.0]);
});

test('a rider who is no longer active stops widening the limit', function () {
    $shop = loadMixedShop();
    onlineRider($shop, 'Rico');
    loadCarRider($shop)->update(['status' => 'inactive']);

    expect(app(RiderLoadLimitService::class)->limitsFor($shop)['max'])->toBe(15.0);
});

test('a heavy pickup can be booked when a car rider exists', function () {
    $shop = loadMixedShop();
    loadCarRider($shop);

    expect(loadCustomerPickup($shop, 30)->pickup_type)->toBe('pickup');
});

test('a pickup beyond the biggest vehicle is still refused', function () {
    $shop = loadMixedShop();
    loadCarRider($shop);

    $errors = loadErrors(fn () => loadCustomerPickup($shop, 45));

    expect($errors['pickup_type'][0] ?? '')->toContain('up to 40 kg');
});

test('the booking page shows the fleet limit', function () {
    $shop = loadMixedShop();
    loadCarRider($shop);
    $customer = User::factory()->create(['role' => AccountType::Customer->value, 'referred_shop_id' => $shop->id]);

    $this->actingAs($customer)->get(route('user.shops.show', $shop))
        ->assertInertia(fn ($page) => $page
            ->where('shop.rider_load_limit.min', null)
            ->where('shop.rider_load_limit.max', 40));
});

// ─── Assignment: the rider's own vehicle ───────────────────────────────────────

test('a heavy delivery goes to the car rider, not the free motorcycle rider', function () {
    $shop = loadMixedShop();
    onlineRider($shop, 'Rico', freeSince: now()->subHour());
    $car = loadCarRider($shop);

    $delivery = app(LogisticsService::class)->dispatchCompletedOrder(loadCompletedOrder($shop, 30));

    expect($delivery->rider_id)->toBe($car->id)
        ->and($delivery->status)->toBe('assigned');
});

test('a heavy delivery waits when only a motorcycle rider is free', function () {
    $shop = loadMixedShop();
    onlineRider($shop, 'Rico');
    $car = loadCarRider($shop);
    $car->update(['is_online' => false]);

    $delivery = app(LogisticsService::class)->dispatchCompletedOrder(loadCompletedOrder($shop, 30));

    expect($delivery->rider_id)->toBeNull()
        ->and($delivery->status)->toBe('pending');

    // The car rider comes back online and is given the waiting run.
    $car->update(['is_online' => true, 'available_since' => now()]);
    app(LogisticsService::class)->assignWaitingDeliveries($shop->id);

    expect($delivery->fresh()->rider_id)->toBe($car->id);
});

test('a light delivery still goes to the longest-free rider of any vehicle', function () {
    $shop = loadMixedShop();
    $moto = onlineRider($shop, 'Rico', freeSince: now()->subHour());
    loadCarRider($shop);

    $delivery = app(LogisticsService::class)->dispatchCompletedOrder(loadCompletedOrder($shop, 6));

    expect($delivery->rider_id)->toBe($moto->id);
});

test('staff cannot hand a run to a rider whose vehicle cannot carry it', function () {
    $shop = loadMixedShop();
    $car = loadCarRider($shop);
    $delivery = app(LogisticsService::class)->dispatchCompletedOrder(loadCompletedOrder($shop, 30));
    $delivery->update(['rider_id' => null, 'status' => 'pending']);
    $moto = onlineRider($shop, 'Rico');

    $errors = loadErrors(fn () => app(LogisticsService::class)
        ->updateStatus($shop->owner, $delivery->fresh(), ['status' => 'assigned', 'rider_id' => $moto->id]));

    expect($errors['rider_id'][0] ?? '')->toContain('Motorcycle')->toContain('15 kg')
        ->and($delivery->fresh()->rider_id)->toBeNull();

    app(LogisticsService::class)->updateStatus($shop->owner, $delivery->fresh(), ['status' => 'assigned', 'rider_id' => $car->id]);

    expect($delivery->fresh()->rider_id)->toBe($car->id);
});

test('the board flags a run whose rider vehicle is too small', function () {
    $shop = loadMixedShop();
    loadCarRider($shop);
    $delivery = app(LogisticsService::class)->dispatchCompletedOrder(loadCompletedOrder($shop, 30));
    $moto = onlineRider($shop, 'Rico');
    $delivery->update(['rider_id' => $moto->id, 'status' => 'assigned']);

    $board = app(LogisticsService::class)->board($shop->owner, null);
    $warnings = collect($board['deliveries']->items())->pluck('load_warning')->filter()->values()->all();

    expect($warnings)->toHaveCount(1)->and($warnings[0])->toContain('Rico')->toContain('Motorcycle');
});

test('a rider with no vehicle type or a removed one is held to the motorcycle capacity', function () {
    $shop = loadMixedShop();
    $service = app(RiderLoadLimitService::class);

    expect($service->limitForType($shop, null))->toBe(['min' => 3.0, 'max' => 15.0])
        ->and($service->limitForType($shop, 'Truck'))->toBe(['min' => 3.0, 'max' => 15.0])
        ->and($service->limitForType($shop, 'Car'))->toBe(['min' => null, 'max' => 40.0]);
});

test('each vehicle capacity must be sensible', function () {
    $shop = loadShop(null, null);

    $this->actingAs($shop->owner)
        ->patch('/shop/settings/rider-load', [
            'rider_vehicle_types' => ['Motorcycle', 'Car'],
            'rider_vehicle_limits' => [
                ['type' => 'Motorcycle', 'min' => '3', 'max' => '15'],
                ['type' => 'Car', 'min' => '20', 'max' => '10'],
            ],
        ])
        ->assertSessionHasErrors('rider_vehicle_limits.1.max');
});

test('the forms list what each rider vehicle can carry', function () {
    $shop = loadMixedShop();
    onlineRider($shop, 'Rico');
    loadCarRider($shop);
    $customer = User::factory()->create(['role' => AccountType::Customer->value, 'referred_shop_id' => $shop->id]);
    $expected = [
        ['type' => 'Motorcycle', 'min' => 3, 'max' => 15],
        ['type' => 'Car', 'min' => null, 'max' => 40],
    ];

    $this->actingAs($customer)->get(route('user.shops.show', $shop))
        ->assertInertia(fn ($page) => $page->where('shop.rider_vehicle_ranges', $expected));

    $this->actingAs($shop->owner)->get('/shop/operations/orders/create')
        ->assertInertia(fn ($page) => $page->where('riderVehicleRanges', $expected));
});

test('a rider with no shift now does not set the limit or show in the ranges', function () {
    $shop = loadMixedShop();
    // Rico has no shift today and has not clocked in; Henry (car) is on duty.
    onlineRider($shop, 'Rico', scheduled: false, clockedIn: false);
    loadCarRider($shop);
    $service = app(RiderLoadLimitService::class);

    expect($service->limitsFor($shop))->toBe(['min' => null, 'max' => 40.0])
        ->and($service->vehicleRangesFor($shop))->toBe([['type' => 'Car', 'min' => null, 'max' => 40.0]]);
});

test('with nobody on duty the limit falls back to every active rider', function () {
    $shop = loadMixedShop();
    onlineRider($shop, 'Rico', scheduled: false, clockedIn: false);

    expect(app(RiderLoadLimitService::class)->limitsFor($shop))->toBe(['min' => 3.0, 'max' => 15.0]);
});
