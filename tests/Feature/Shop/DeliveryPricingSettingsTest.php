<?php

use App\Enums\AccountType;
use App\Http\Middleware\EnforceOwnerPlatformPermissions;
use App\Http\Middleware\EnsureCurrentBusinessAgreementAccepted;
use App\Models\Order;
use App\Models\Shop;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

function pricingOwnerShop(array $overrides = []): array
{
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
    $shop = Shop::create(array_merge([
        'owner_id' => $owner->id,
        'shop_name' => 'Pricing Laundry '.$owner->id,
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

beforeEach(function () {
    $this->withoutMiddleware([
        EnsureCurrentBusinessAgreementAccepted::class,
        EnforceOwnerPlatformPermissions::class,
    ]);
});

test('the settings page exposes the delivery pricing', function () {
    [$owner] = pricingOwnerShop(['delivery_fee_mode' => 'per_km', 'delivery_per_km_rate' => 12]);

    $this->actingAs($owner)
        ->get(route('shop.settings'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('shop/Settings')
            ->where('deliveryPricing.offers_delivery', true)
            ->where('deliveryPricing.has_location', true)
            ->where('deliveryPricing.delivery_fee_mode', 'per_km')
            ->where('deliveryPricing.delivery_per_km_rate', 12)
            ->where('deliveryPricing.delivery_max_km', null));
});

test('an owner saves fixed rate pricing', function () {
    [$owner, $shop] = pricingOwnerShop();

    $this->actingAs($owner)
        ->patch(route('shop.settings.delivery-pricing'), [
            'delivery_fee_mode' => 'fixed',
            'delivery_fixed_fee' => 49.5,
            'delivery_max_km' => '',
        ])
        ->assertSessionHas('toast', ['type' => 'success', 'message' => 'Delivery pricing saved.']);

    $shop->refresh();
    expect($shop->delivery_fee_mode)->toBe('fixed')
        ->and((float) $shop->delivery_fixed_fee)->toBe(49.5)
        ->and($shop->delivery_max_km)->toBeNull();
});

test('an owner saves per kilometer pricing without losing the fixed fee', function () {
    [$owner, $shop] = pricingOwnerShop(['delivery_fixed_fee' => 60]);

    $this->actingAs($owner)
        ->patch(route('shop.settings.delivery-pricing'), [
            'delivery_fee_mode' => 'per_km',
            'delivery_base_fee' => 25,
            'delivery_per_km_rate' => 8,
            'delivery_free_km' => 1.5,
            'delivery_min_fee' => 40,
            'delivery_max_km' => 12,
        ])
        ->assertSessionHas('toast.type', 'success');

    $shop->refresh();
    expect($shop->delivery_fee_mode)->toBe('per_km')
        ->and((float) $shop->delivery_base_fee)->toBe(25.0)
        ->and((float) $shop->delivery_per_km_rate)->toBe(8.0)
        ->and((float) $shop->delivery_free_km)->toBe(1.5)
        ->and((float) $shop->delivery_min_fee)->toBe(40.0)
        ->and((float) $shop->delivery_max_km)->toBe(12.0)
        ->and((float) $shop->delivery_fixed_fee)->toBe(60.0);
});

test('pricing fields are validated per mode', function () {
    [$owner] = pricingOwnerShop();

    $this->actingAs($owner)
        ->patch(route('shop.settings.delivery-pricing'), ['delivery_fee_mode' => 'fixed'])
        ->assertSessionHasErrors(['delivery_fixed_fee'])
        ->assertSessionDoesntHaveErrors(['delivery_base_fee', 'delivery_per_km_rate']);

    $this->actingAs($owner)
        ->patch(route('shop.settings.delivery-pricing'), ['delivery_fee_mode' => 'per_km'])
        ->assertSessionHasErrors(['delivery_base_fee', 'delivery_per_km_rate'])
        ->assertSessionDoesntHaveErrors(['delivery_fixed_fee']);

    $this->actingAs($owner)
        ->patch(route('shop.settings.delivery-pricing'), [
            'delivery_fee_mode' => 'per_km',
            'delivery_base_fee' => -1,
            'delivery_per_km_rate' => 5000,
            'delivery_max_km' => 0,
        ])
        ->assertSessionHasErrors(['delivery_base_fee', 'delivery_per_km_rate', 'delivery_max_km']);

    $this->actingAs($owner)
        ->patch(route('shop.settings.delivery-pricing'), ['delivery_fee_mode' => 'distance'])
        ->assertSessionHasErrors(['delivery_fee_mode']);
});

test('an owner only changes their own shop pricing', function () {
    [$ownerA, $shopA] = pricingOwnerShop(['delivery_fixed_fee' => 20]);
    [, $shopB] = pricingOwnerShop(['delivery_fixed_fee' => 99]);

    // Pointing at another shop is refused outright by the capability guard.
    $this->actingAs($ownerA)
        ->patch(route('shop.settings.delivery-pricing'), [
            'delivery_fee_mode' => 'fixed',
            'delivery_fixed_fee' => 10,
            'shop_id' => $shopB->id,
        ])
        ->assertForbidden();

    expect((float) $shopB->fresh()->delivery_fixed_fee)->toBe(99.0);

    // The owner's shop always comes from the session, never from input.
    $this->actingAs($ownerA)
        ->patch(route('shop.settings.delivery-pricing'), ['delivery_fee_mode' => 'fixed', 'delivery_fixed_fee' => 10])
        ->assertSessionHas('toast.type', 'success');

    expect((float) $shopA->fresh()->delivery_fixed_fee)->toBe(10.0)
        ->and((float) $shopB->fresh()->delivery_fixed_fee)->toBe(99.0);
});

test('pricing cannot be saved when the shop does not offer delivery', function () {
    [$owner] = pricingOwnerShop(['offers_delivery' => false]);

    $this->actingAs($owner)
        ->patch(route('shop.settings.delivery-pricing'), ['delivery_fee_mode' => 'fixed', 'delivery_fixed_fee' => 10])
        ->assertForbidden();
});

test('customers and staff cannot change delivery pricing', function (AccountType $role) {
    [, $shop] = pricingOwnerShop(['delivery_fixed_fee' => 30]);
    $user = User::factory()->create(['role' => $role->value]);

    // The owner-only role guard turns other roles away before the controller runs.
    $this->actingAs($user)
        ->patch(route('shop.settings.delivery-pricing'), ['delivery_fee_mode' => 'fixed', 'delivery_fixed_fee' => 10])
        ->assertRedirect();

    expect((float) $shop->fresh()->delivery_fixed_fee)->toBe(30.0);
})->with([AccountType::Customer, AccountType::Staff]);
