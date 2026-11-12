<?php

use App\Enums\AccountType;
use App\Http\Middleware\EnforceOwnerPlatformPermissions;
use App\Http\Middleware\EnsureCurrentBusinessAgreementAccepted;
use App\Models\Order;
use App\Models\Shop;
use App\Models\ShopOrder;
use App\Models\ShopService;
use App\Models\User;
use App\Services\CustomerAgreementService;
use App\Services\DistanceService;

beforeEach(function () {
    $this->withoutMiddleware([
        EnsureCurrentBusinessAgreementAccepted::class,
        EnforceOwnerPlatformPermissions::class,
    ]);

    // The route distance is a Mapbox call; pin it to 4 km for every quote.
    $this->mock(DistanceService::class)
        ->shouldReceive('between')
        ->andReturn(['distance_km' => 4.0, 'source' => 'haversine']);
});

/** A set-up shop on a paid plan that delivers, priced as given. */
function counterDeliveryShop(array $pricing = []): Shop
{
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
    $shop = Shop::create(array_merge([
        'owner_id' => $owner->id,
        'shop_name' => 'Counter Laundry',
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Poblacion',
        'postal_code' => '4103',
        'status' => 'active',
        'setup_completed_at' => now(),
        'offers_pickup' => true,
        'offers_delivery' => true,
        'delivery_pricing_set_at' => now(),
        'delivery_fee_mode' => Shop::DELIVERY_FEE_FIXED,
        'delivery_fixed_fee' => 60,
        'latitude' => 14.43,
        'longitude' => 120.93,
    ], $pricing));
    Order::create([
        'user_id' => $owner->id, 'shop_name' => $shop->shop_name, 'owner_name' => $owner->name,
        'email' => $owner->email, 'phone' => '09171234567', 'municipality' => 'Imus',
        'barangay' => 'Poblacion', 'postal_code' => '4103', 'plan_name' => 'Premium',
        'billing_months' => 1, 'is_trial' => false, 'total_price' => 2239, 'status' => 'paid',
        'expires_at' => now()->addMonth(),
    ]);

    return $shop;
}

function counterOrderPayload(Shop $shop, array $overrides = []): array
{
    $service = ShopService::firstOrCreate(
        ['shop_id' => $shop->id, 'service_name' => 'Wash and Fold'],
        ['is_active' => true, 'pricing_model' => 'per_kg', 'price_per_kg' => 60],
    );

    return array_merge([
        'shop_id' => $shop->id,
        'customer_name' => 'Walk-in Juan',
        'customer_phone' => '09171234567',
        'customer_address' => '12 Rizal St, Imus',
        'alternate_contact_name' => 'Aling Nena',
        'alternate_contact_phone' => '09181234567',
        'service_id' => $service->id,
        'pickup_type' => 'walk_in',
        'deliver_to_customer' => true,
        'pricing_model' => 'per_kg',
        'price_per_kg' => 60,
        'actual_weight_kg' => 3,
        'total_amount' => 180,
        'amount_paid' => 0,
        'customer_agreement_version' => app(CustomerAgreementService::class)->agreementDataForShopId($shop->id)['version'],
        'customer_agreement_accepted' => true,
    ], $overrides);
}

test('a walk-in order sent back by delivery adds the fixed delivery fee to the total', function () {
    $shop = counterDeliveryShop();

    $this->actingAs($shop->owner)
        ->post('/shop/operations/orders', counterOrderPayload($shop))
        ->assertSessionHasNoErrors();

    $order = ShopOrder::where('shop_id', $shop->id)->sole();

    expect((float) $order->delivery_fee)->toBe(60.0)
        ->and((float) $order->total_amount)->toBe(240.0);
});

test('the delivery fee is added after tax', function () {
    $shop = counterDeliveryShop();
    $shop->forceFill(['tax_type' => 'vat'])->save();
    $rate = app(App\Services\OrderTaxService::class)->rateForShop($shop->id);

    $this->actingAs($shop->owner)
        ->post('/shop/operations/orders', counterOrderPayload($shop))
        ->assertSessionHasNoErrors();

    $order = ShopOrder::where('shop_id', $shop->id)->sole();
    $tax = round(180 * $rate / 100, 2);

    expect((float) $order->tax_amount)->toBe($tax)
        ->and((float) $order->total_amount)->toBe(round(180 + $tax + 60, 2));
});

test('a walk-in the customer collects has no delivery fee', function () {
    $shop = counterDeliveryShop();

    $this->actingAs($shop->owner)
        ->post('/shop/operations/orders', counterOrderPayload($shop, ['deliver_to_customer' => false]))
        ->assertSessionHasNoErrors();

    $order = ShopOrder::where('shop_id', $shop->id)->sole();

    expect((float) $order->delivery_fee)->toBe(0.0)
        ->and((float) $order->total_amount)->toBe(180.0);
});

test('per-km delivery is priced from the pinned customer location', function () {
    $shop = counterDeliveryShop([
        'delivery_fee_mode' => Shop::DELIVERY_FEE_PER_KM,
        'delivery_base_fee' => 30,
        'delivery_per_km_rate' => 10,
        'delivery_free_km' => 1,
        'delivery_min_fee' => 0,
    ]);

    $this->actingAs($shop->owner)
        ->post('/shop/operations/orders', counterOrderPayload($shop, [
            'customer_lat' => 14.45,
            'customer_lng' => 120.95,
        ]))
        ->assertSessionHasNoErrors();

    $order = ShopOrder::where('shop_id', $shop->id)->sole();

    // 30 base + (4 km - 1 free km) × 10 = 60.
    expect((float) $order->delivery_fee)->toBe(60.0)
        ->and((float) $order->total_amount)->toBe(240.0)
        ->and($order->customer_lat)->toBe(14.45);
});

test('per-km delivery needs the customer location pinned', function () {
    $shop = counterDeliveryShop(['delivery_fee_mode' => Shop::DELIVERY_FEE_PER_KM, 'delivery_per_km_rate' => 10]);

    $this->actingAs($shop->owner)
        ->post('/shop/operations/orders', counterOrderPayload($shop))
        ->assertSessionHasErrors(['customer_lat']);

    expect(ShopOrder::where('shop_id', $shop->id)->exists())->toBeFalse();
});

test('a payment cannot be more than the total with the delivery fee', function () {
    $shop = counterDeliveryShop();

    $this->actingAs($shop->owner)
        ->post('/shop/operations/orders', counterOrderPayload($shop, ['amount_paid' => 240, 'payment_method' => 'cash']))
        ->assertSessionHasNoErrors();

    expect(ShopOrder::where('shop_id', $shop->id)->sole()->payment_status)->toBe('paid');
});

test('the order form gets the shop delivery pricing', function () {
    $shop = counterDeliveryShop();

    $this->actingAs($shop->owner)->get('/shop/operations/orders/create')
        ->assertInertia(fn ($page) => $page
            ->where('deliveryPricing.available', true)
            ->where('deliveryPricing.mode', 'fixed')
            ->where('deliveryPricing.fixed_fee', 60)
            ->where('deliveryPricing.needs_location', false));
});

test('the delivery quote endpoint prices a pinned location', function () {
    $shop = counterDeliveryShop([
        'delivery_fee_mode' => Shop::DELIVERY_FEE_PER_KM,
        'delivery_base_fee' => 30,
        'delivery_per_km_rate' => 10,
        'delivery_free_km' => 1,
    ]);

    $this->actingAs($shop->owner)
        ->postJson('/shop/operations/orders/delivery-quote', ['lat' => 14.45, 'lng' => 120.95])
        ->assertOk()
        ->assertJson(['available' => true, 'fee' => 60, 'distance_km' => 4]);
});
