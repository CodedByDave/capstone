<?php

use App\Enums\AccountType;
use App\Http\Middleware\EnforceOwnerPlatformPermissions;
use App\Http\Middleware\EnsureCurrentBusinessAgreementAccepted;
use App\Models\Order;
use App\Models\Shop;
use App\Models\ShopCustomer;
use App\Models\ShopService;
use App\Models\User;
use App\Services\ShopOrderService;

beforeEach(function () {
    $this->withoutMiddleware([
        EnsureCurrentBusinessAgreementAccepted::class,
        EnforceOwnerPlatformPermissions::class,
    ]);
});

function customerShop(): array
{
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
    $shop = Shop::create([
        'owner_id' => $owner->id,
        'shop_name' => 'Customer Laundry '.$owner->id,
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Poblacion',
        'postal_code' => '4103',
        'status' => 'active',
        'setup_completed_at' => now(),
    ]);
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
    $service = ShopService::create([
        'shop_id' => $shop->id, 'service_name' => 'Wash', 'is_active' => true,
        'pricing_model' => 'per_kg', 'price_per_kg' => 60,
    ]);

    return [$owner->fresh(), $shop, $service];
}

function placeCounterOrder(User $owner, Shop $shop, ShopService $service, string $name, ?string $phone, ?string $email = null)
{
    return app(ShopOrderService::class)->createOrder([
        'shop_id' => $shop->id, 'customer_name' => $name, 'customer_phone' => $phone, 'customer_email' => $email,
        'service_id' => $service->id, 'pickup_type' => 'walk_in', 'pricing_model' => 'per_kg',
        'actual_weight_kg' => 5, 'price_per_kg' => 60, 'additional_charges' => 0,
        'discount_amount' => 0, 'total_amount' => 300,
        'customer_agreement_version' => '1.0', 'customer_agreement_accepted' => true,
    ], $owner);
}

it('keeps one customer for the same name and number however it is typed', function () {
    [$owner, $shop, $service] = customerShop();

    $first = placeCounterOrder($owner, $shop, $service, 'Ramuel Rivera', '0917 555 1234');
    $second = placeCounterOrder($owner, $shop, $service, 'ramuel  rivera', '+63-917-555-1234', 'ramuel@example.com');

    expect(ShopCustomer::where('shop_id', $shop->id)->count())->toBe(1)
        ->and($first->shop_customer_id)->toBe($second->shop_customer_id)
        ->and(ShopCustomer::first()->orders()->count())->toBe(2)
        // The email given on a later order fills in what the first one did not have.
        ->and(ShopCustomer::first()->email)->toBe('ramuel@example.com');
});

it('keeps one customer when the same name gives a different number, and follows the latest number', function () {
    [$owner, $shop, $service] = customerShop();

    $first = placeCounterOrder($owner, $shop, $service, 'Henry', '0961 124 4542');
    $second = placeCounterOrder($owner, $shop, $service, 'henry', '0961 124 3323');

    $customer = ShopCustomer::where('shop_id', $shop->id)->sole();

    expect($first->shop_customer_id)->toBe($second->shop_customer_id)
        ->and($customer->orders()->count())->toBe(2)
        ->and($customer->phone)->toBe('+63-961-1243-323')
        // Each order keeps the number it was placed with.
        ->and($first->fresh()->customer_phone)->toBe('+63-961-1244-542');
});

it('keeps people with different names apart even when they share a number', function () {
    [$owner, $shop, $service] = customerShop();

    placeCounterOrder($owner, $shop, $service, 'Ramuel Rivera', '0917 555 1234');
    placeCounterOrder($owner, $shop, $service, 'Maria Rivera', '0917 555 1234');

    expect(ShopCustomer::where('shop_id', $shop->id)->count())->toBe(2);
});

it('never shares a customer between two shops', function () {
    [$ownerA, $shopA, $serviceA] = customerShop();
    [$ownerB, $shopB, $serviceB] = customerShop();

    $a = placeCounterOrder($ownerA, $shopA, $serviceA, 'Ramuel Rivera', '0917 555 1234');
    $b = placeCounterOrder($ownerB, $shopB, $serviceB, 'Ramuel Rivera', '0917 555 1234');

    expect($a->shop_customer_id)->not->toBe($b->shop_customer_id);

    $other = ShopCustomer::find($b->shop_customer_id);
    $this->actingAs($ownerA)->get("/shop/operations/customers/{$other->public_id}")->assertNotFound();
});

it('lists each customer once with their orders and suggests them while typing', function () {
    [$owner, $shop, $service] = customerShop();
    placeCounterOrder($owner, $shop, $service, 'Ramuel Rivera', '0917 555 1234');
    placeCounterOrder($owner, $shop, $service, 'Ramuel Rivera', '0917 555 9999');
    placeCounterOrder($owner, $shop, $service, 'Maria Santos', '0928 111 2222');

    $this->actingAs($owner)
        ->get('/shop/operations/customers')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/operations/customers/Index')
            ->where('customers.total', 2)
            ->where('summary.returning', 1));

    $customer = ShopCustomer::where('name', 'Ramuel Rivera')->first();

    $this->actingAs($owner)
        ->get("/shop/operations/customers/{$customer->public_id}")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/operations/customers/Show')
            ->where('stats.orders', 2)
            ->where('orders.total', 2));

    $this->actingAs($owner)
        ->getJson('/shop/operations/customers/search?q=ramu')
        ->assertOk()
        ->assertJsonPath('customers.0.name', 'Ramuel Rivera')
        ->assertJsonPath('customers.0.orders', 2);
});

it('does not make a customer of a counter sale with no name and no number', function () {
    [$owner, $shop] = customerShop();
    $product = App\Models\Inventory::create([
        'shop_id' => $shop->id, 'name' => 'Softener', 'unit' => 'ml', 'quantity' => 3000, 'min_stock' => 1,
        'unit_price' => 80, 'status' => 'active', 'retail_enabled' => true, 'retail_name' => 'Softener 1L',
        'retail_pack_size' => 1000, 'retail_price' => 150,
    ]);

    $this->actingAs($owner)->post('/shop/operations/retail', [
        'retail_items' => [['id' => $product->id, 'count' => 1]],
        'payment_method' => 'cash',
    ])->assertRedirect();

    expect(ShopCustomer::count())->toBe(0);

    $this->actingAs($owner)->post('/shop/operations/retail', [
        'retail_items' => [['id' => $product->id, 'count' => 1]],
        'payment_method' => 'cash',
        'customer_name' => 'Ramuel Rivera',
        'customer_phone' => '09175551234',
    ])->assertRedirect();

    expect(ShopCustomer::count())->toBe(1);
});

it('links an order to the customer staff picked from the search, whatever name is typed', function () {
    [$owner, $shop, $service] = customerShop();
    $first = placeCounterOrder($owner, $shop, $service, 'Henry Cruz', '0961 124 4542');
    $picked = ShopCustomer::find($first->shop_customer_id);

    $order = app(ShopOrderService::class)->createOrder([
        'shop_id' => $shop->id, 'customer_name' => 'H. Cruz', 'customer_phone' => '0961 124 3323',
        'shop_customer_id' => $picked->public_id,
        'service_id' => $service->id, 'pickup_type' => 'walk_in', 'pricing_model' => 'per_kg',
        'actual_weight_kg' => 5, 'price_per_kg' => 60, 'additional_charges' => 0,
        'discount_amount' => 0, 'total_amount' => 300,
        'customer_agreement_version' => '1.0', 'customer_agreement_accepted' => true,
    ], $owner);

    expect(ShopCustomer::count())->toBe(1)
        ->and($order->shop_customer_id)->toBe($picked->id)
        // The order carries the picked customer's own name, so one customer has one name.
        ->and($order->customer_name)->toBe('Henry Cruz');
});

it('refuses a picked customer that belongs to another shop', function () {
    [$ownerA, $shopA, $serviceA] = customerShop();
    [$ownerB, $shopB, $serviceB] = customerShop();
    $foreign = ShopCustomer::find(placeCounterOrder($ownerB, $shopB, $serviceB, 'Henry Cruz', '0961 124 4542')->shop_customer_id);

    expect(fn () => placeCounterOrder($ownerA, $shopA, $serviceA, 'Henry Cruz', '0961 124 4542') && app(ShopOrderService::class)->createOrder([
        'shop_id' => $shopA->id, 'customer_name' => 'Henry Cruz', 'customer_phone' => '0961 124 4542',
        'shop_customer_id' => $foreign->public_id,
        'service_id' => $serviceA->id, 'pickup_type' => 'walk_in', 'pricing_model' => 'per_kg',
        'actual_weight_kg' => 5, 'price_per_kg' => 60, 'additional_charges' => 0,
        'discount_amount' => 0, 'total_amount' => 300,
        'customer_agreement_version' => '1.0', 'customer_agreement_accepted' => true,
    ], $ownerA))->toThrow(Illuminate\Validation\ValidationException::class);
});

it('finds a customer by phone however the number is typed', function () {
    [$owner, $shop, $service] = customerShop();
    placeCounterOrder($owner, $shop, $service, 'Henry Cruz', '0961 124 4542');

    foreach (['0961 124', '961124', '+63 961 1244', 'henry'] as $term) {
        $this->actingAs($owner)
            ->getJson('/shop/operations/customers/search?q='.urlencode($term))
            ->assertJsonPath('customers.0.name', 'Henry Cruz');
    }
});
