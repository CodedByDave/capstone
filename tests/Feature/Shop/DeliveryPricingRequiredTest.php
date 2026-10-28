<?php

use App\Enums\AccountType;
use App\Exceptions\DeliveryNotAvailableException;
use App\Http\Middleware\EnforceOwnerPlatformPermissions;
use App\Http\Middleware\EnsureCurrentBusinessAgreementAccepted;
use App\Models\Employee;
use App\Models\EmployeeRole;
use App\Models\Order;
use App\Models\RolePermission;
use App\Models\Shop;
use App\Models\ShopOrder;
use App\Models\ShopService;
use App\Models\User;
use App\Services\DeliveryFeeService;
use App\Services\LogisticsService;
use App\Services\ShopSetupService;

beforeEach(function () {
    $this->withoutMiddleware([
        EnsureCurrentBusinessAgreementAccepted::class,
        EnforceOwnerPlatformPermissions::class,
    ]);
});

/** A set-up shop that offers delivery but has not saved its delivery pricing. */
function unpricedShop(array $overrides = []): array
{
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
    $shop = Shop::create(array_merge([
        'owner_id' => $owner->id,
        'shop_name' => 'Unpriced Laundry '.$owner->id,
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Poblacion',
        'postal_code' => '4103',
        'status' => 'active',
        'location_mode' => 'single',
        'setup_completed_at' => now(),
        'offers_pickup' => true,
        'offers_delivery' => true,
        'latitude' => 14.4296,
        'longitude' => 120.9367,
    ], $overrides));
    Order::create([
        'user_id' => $owner->id,
        'shop_name' => $shop->shop_name,
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

    return [$owner, $shop];
}

function unpricedStaff(Shop $shop, array $permissions): User
{
    $user = User::factory()->create(['role' => AccountType::Staff->value]);
    $employee = Employee::create([
        'shop_id' => $shop->id,
        'user_id' => $user->id,
        'employee_id' => 'PR-'.$user->id,
        'first_name' => 'Staff',
        'last_name' => 'Member',
        'position' => 'Staff',
        'hire_date' => today(),
        'status' => 'Active',
    ]);
    EmployeeRole::create(['employee_id' => $employee->id, 'role' => 'dispatcher']);

    foreach ($permissions as [$module, $action]) {
        RolePermission::create(['shop_id' => $shop->id, 'role' => 'dispatcher', 'module' => $module, 'action' => $action]);
    }

    return $user;
}

function unpricedCompletedOrder(Shop $shop, array $overrides = []): ShopOrder
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
        'order_source' => 'shop',
        'service_id' => $service->id,
        'order_number' => 'WLK-'.strtoupper(uniqid()),
        'customer_name' => 'Maria Cruz',
        'customer_phone' => '09181234567',
        'customer_address' => '12 Rizal St, Imus',
        'pickup_type' => 'pickup',
        'pricing_model' => 'per_kg',
        'price_per_kg' => 60,
        'estimated_weight_kg' => 3,
        'total_amount' => 180,
        'payment_status' => 'unpaid',
        'status' => 'completed',
    ], $overrides));
}

// ── The owner is forced to set pricing ───────────────────────────────────────

test('an owner who offers delivery is sent to settings until the pricing is set', function () {
    [$owner] = unpricedShop();

    foreach (['/shop/dashboard', '/shop/operations/orders', '/shop/logistics'] as $path) {
        $this->actingAs($owner)->get($path)->assertRedirect(route('shop.settings'));
    }
});

test('the settings page stays reachable and says the pricing is required', function () {
    [$owner] = unpricedShop();

    $this->actingAs($owner)
        ->get(route('shop.settings'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('deliveryPricing.pricing_required', true)
            ->where('deliveryPricing.is_configured', false));
});

test('saving a real fee unlocks the app and records when pricing was set', function () {
    [$owner, $shop] = unpricedShop();

    $this->actingAs($owner)
        ->patch(route('shop.settings.delivery-pricing'), ['delivery_fee_mode' => 'fixed', 'delivery_fixed_fee' => 60])
        ->assertSessionHasNoErrors();

    expect($shop->fresh())->delivery_pricing_set_at->not->toBeNull();

    $this->actingAs($owner)->get('/shop/dashboard')->assertOk();
});

test('delivery pricing must actually charge something', function () {
    [$owner, $shop] = unpricedShop();

    $this->actingAs($owner)
        ->patch(route('shop.settings.delivery-pricing'), ['delivery_fee_mode' => 'fixed', 'delivery_fixed_fee' => 0])
        ->assertSessionHasErrors('delivery_fixed_fee');

    $this->actingAs($owner)
        ->patch(route('shop.settings.delivery-pricing'), [
            'delivery_fee_mode' => 'per_km', 'delivery_base_fee' => 0, 'delivery_per_km_rate' => 0,
        ])
        ->assertSessionHasErrors('delivery_per_km_rate');

    expect($shop->fresh()->delivery_pricing_set_at)->toBeNull();
});

test('a per kilometer fee needs only a base fee or a rate above zero', function () {
    [$owner, $shop] = unpricedShop();

    $this->actingAs($owner)
        ->patch(route('shop.settings.delivery-pricing'), [
            'delivery_fee_mode' => 'per_km', 'delivery_base_fee' => 0, 'delivery_per_km_rate' => 12,
        ])
        ->assertSessionHasNoErrors();

    expect($shop->fresh()->hasDeliveryPricing())->toBeTrue();
});

test('shops that do not offer delivery are never forced', function () {
    [$owner] = unpricedShop(['offers_delivery' => false]);

    $this->actingAs($owner)->get('/shop/dashboard')->assertOk();
});

test('later edits keep the time the pricing was first set', function () {
    [$owner, $shop] = unpricedShop(['delivery_pricing_set_at' => now()->subDays(3)]);
    $first = $shop->delivery_pricing_set_at->toDateTimeString();

    $this->actingAs($owner)
        ->patch(route('shop.settings.delivery-pricing'), ['delivery_fee_mode' => 'fixed', 'delivery_fixed_fee' => 80])
        ->assertSessionHasNoErrors();

    expect($shop->fresh()->delivery_pricing_set_at->toDateTimeString())->toBe($first);
});

// ── Nothing delivers while the pricing is missing ────────────────────────────

test('staff cannot open logistics or send orders for delivery without pricing', function () {
    [, $shop] = unpricedShop();
    $staff = unpricedStaff($shop, [['Logistics', 'view'], ['Operations', 'manage']]);
    $order = unpricedCompletedOrder($shop, ['pickup_type' => 'walk_in']);

    $this->actingAs($staff)->get('/staff/logistics')->assertForbidden();
    $this->actingAs($staff)->post("/staff/operations/orders/{$order->payment_public_id}/send-for-delivery")->assertForbidden();

    expect(App\Models\Delivery::count())->toBe(0);
});

test('automatic deliveries and pickup runs are not created without pricing', function () {
    [, $shop] = unpricedShop();
    $service = app(LogisticsService::class);

    $service->dispatchPickupRun(unpricedCompletedOrder($shop, ['status' => 'pending']));
    $service->dispatchCompletedOrder(unpricedCompletedOrder($shop));

    expect(App\Models\Delivery::count())->toBe(0);
});

test('the order form refuses deliver to customer without pricing', function () {
    [, $shop] = unpricedShop();
    $staff = unpricedStaff($shop, [['Operations', 'manage']]);
    $order = unpricedCompletedOrder($shop, ['pickup_type' => 'walk_in', 'status' => 'in_progress']);

    $this->actingAs($staff)
        ->put("/staff/operations/orders/{$order->payment_public_id}", [
            'customer_name' => 'Maria Cruz',
            'customer_address' => '12 Rizal St, Imus',
            'deliver_to_customer' => true,
            'service_id' => $order->service_id,
            'pickup_type' => 'walk_in',
            'pricing_model' => 'per_kg',
            'estimated_weight_kg' => 3,
            'actual_weight_kg' => 3,
            'price_per_kg' => 60,
            'total_amount' => 180,
            'payment_method' => 'cash',
            'payment_status' => 'unpaid',
            'status' => 'in_progress',
        ])
        ->assertSessionHasErrors('deliver_to_customer');

    expect($order->fresh()->deliver_to_customer)->toBeFalse();
});

test('customers cannot be quoted a delivery fee before the shop sets its pricing', function () {
    [, $shop] = unpricedShop();

    expect(fn () => app(DeliveryFeeService::class)->assertShopCanQuote($shop))
        ->toThrow(DeliveryNotAvailableException::class, 'has not set its delivery pricing');
});

test('shop capabilities report delivery as unavailable and pricing as required', function () {
    [, $unpriced] = unpricedShop();
    [, $priced] = unpricedShop(['delivery_pricing_set_at' => now()]);
    $setup = app(ShopSetupService::class);

    expect($setup->capabilitiesForShop($unpriced))
        ->offers_delivery->toBeFalse()
        ->delivery_pricing_required->toBeTrue()
        ->and($setup->capabilitiesForShop($priced))
        ->offers_delivery->toBeTrue()
        ->delivery_pricing_required->toBeFalse();
});
