<?php

use App\Enums\AccountType;
use App\Http\Middleware\EnforceOwnerPlatformPermissions;
use App\Http\Middleware\EnsureCurrentBusinessAgreementAccepted;
use App\Models\Delivery;
use App\Models\Order;
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
        'rider_min_weight_kg' => $min,
        'rider_max_weight_kg' => $max,
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

test('the owner sets the rider load limit', function () {
    $shop = loadShop(null, null);

    $this->actingAs($shop->owner)
        ->patch('/shop/settings/rider-load', ['rider_min_weight_kg' => '2.5', 'rider_max_weight_kg' => '20'])
        ->assertSessionHasNoErrors();

    $shop->refresh();
    expect((float) $shop->rider_min_weight_kg)->toBe(2.5)
        ->and((float) $shop->rider_max_weight_kg)->toBe(20.0);
});

test('empty fields remove the limit', function () {
    $shop = loadShop();

    $this->actingAs($shop->owner)
        ->patch('/shop/settings/rider-load', ['rider_min_weight_kg' => '', 'rider_max_weight_kg' => ''])
        ->assertSessionHasNoErrors();

    $shop->refresh();
    expect($shop->rider_min_weight_kg)->toBeNull()
        ->and($shop->rider_max_weight_kg)->toBeNull();
});

test('only a maximum can be set', function () {
    $shop = loadShop(null, null);

    $this->actingAs($shop->owner)
        ->patch('/shop/settings/rider-load', ['rider_min_weight_kg' => '', 'rider_max_weight_kg' => '12'])
        ->assertSessionHasNoErrors();

    expect($shop->fresh()->rider_min_weight_kg)->toBeNull()
        ->and((float) $shop->fresh()->rider_max_weight_kg)->toBe(12.0);
});

test('the rider load limit must be sensible', function (array $input, string $field) {
    $shop = loadShop(null, null);

    $this->actingAs($shop->owner)
        ->patch('/shop/settings/rider-load', $input)
        ->assertSessionHasErrors($field);

    expect($shop->fresh()->rider_max_weight_kg)->toBeNull();
})->with([
    'max below min' => [['rider_min_weight_kg' => '10', 'rider_max_weight_kg' => '5'], 'rider_max_weight_kg'],
    'zero minimum' => [['rider_min_weight_kg' => '0', 'rider_max_weight_kg' => '5'], 'rider_min_weight_kg'],
    'too heavy' => [['rider_min_weight_kg' => '', 'rider_max_weight_kg' => '500'], 'rider_max_weight_kg'],
    'not a number' => [['rider_min_weight_kg' => 'abc', 'rider_max_weight_kg' => ''], 'rider_min_weight_kg'],
]);

test('staff cannot change the rider load limit', function () {
    $shop = loadShop(null, null);
    $staff = User::factory()->create(['role' => AccountType::Staff->value]);

    $this->actingAs($staff)->patch('/shop/settings/rider-load', ['rider_max_weight_kg' => '5']);

    expect($shop->fresh()->rider_max_weight_kg)->toBeNull();
});

test('each owner only changes their own shop', function () {
    $mine = loadShop(null, null, 'Mine');
    $other = loadShop(null, null, 'Other');

    $this->actingAs($mine->owner)
        ->patch('/shop/settings/rider-load', ['rider_max_weight_kg' => '9'])
        ->assertSessionHasNoErrors();

    expect((float) $mine->fresh()->rider_max_weight_kg)->toBe(9.0)
        ->and($other->fresh()->rider_max_weight_kg)->toBeNull();
});

test('the settings page shows the limit', function () {
    $shop = loadShop(3, 15);

    $this->actingAs($shop->owner)->get('/shop/settings')
        ->assertInertia(fn ($page) => $page
            ->where('riderLoadLimit.min', 3)
            ->where('riderLoadLimit.max', 15));
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
