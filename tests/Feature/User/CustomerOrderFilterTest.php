<?php

use App\Enums\AccountType;
use App\Models\Shop;
use App\Models\ShopOrder;
use App\Models\ShopService;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

function filterShop(string $name = 'Filter Laundry'): Shop
{
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);

    return Shop::create([
        'owner_id' => $owner->id,
        'shop_name' => $name,
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Poblacion',
        'postal_code' => '4103',
        'status' => 'active',
        'setup_completed_at' => now(),
    ]);
}

function filterCustomer(): User
{
    return User::factory()->create(['role' => AccountType::Customer->value]);
}

function filterOrder(Shop $shop, User $customer, array $overrides = []): ShopOrder
{
    $service = ShopService::firstOrCreate(['shop_id' => $shop->id, 'service_name' => 'Wash and Fold'], [
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
        'pickup_type' => 'walk_in',
        'pricing_model' => 'per_kg',
        'price_per_kg' => 60,
        'estimated_weight_kg' => 3,
        'total_amount' => 180,
        'payment_status' => 'unpaid',
        'amount_paid' => 0,
        'status' => 'pending',
    ], $overrides));
}

test('the list shows all of the customer\'s orders and per-tab counts', function () {
    $me = filterCustomer();
    $shop = filterShop();
    filterOrder($shop, $me, ['status' => 'pending']);
    filterOrder($shop, $me, ['status' => 'in_progress']);
    filterOrder($shop, $me, ['status' => 'completed', 'payment_status' => 'paid']);
    filterOrder($shop, $me, ['status' => 'picked_up', 'payment_status' => 'paid']);
    filterOrder($shop, $me, ['status' => 'cancelled']);
    filterOrder($shop, filterCustomer()); // someone else's

    $this->actingAs($me)
        ->get(route('user.orders.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('user/Orders')
            ->where('orders.total', 5)
            ->where('counts', ['all' => 5, 'pending' => 1, 'in_progress' => 1, 'completed' => 2, 'cancelled' => 1])
            ->where('filters', ['status' => null, 'payment' => null, 'search' => null]));
});

test('the status tab filters the list and "completed" includes picked up orders', function () {
    $me = filterCustomer();
    $shop = filterShop();
    filterOrder($shop, $me, ['status' => 'pending']);
    filterOrder($shop, $me, ['status' => 'completed']);
    filterOrder($shop, $me, ['status' => 'picked_up']);

    $this->actingAs($me)
        ->get(route('user.orders.index', ['status' => 'pending']))
        ->assertInertia(fn (Assert $page) => $page
            ->where('orders.total', 1)
            ->where('orders.data.0.status', 'pending')
            ->where('filters.status', 'pending'));

    $this->actingAs($me)
        ->get(route('user.orders.index', ['status' => 'completed']))
        ->assertInertia(fn (Assert $page) => $page
            ->where('orders.total', 2)
            ->where('counts.all', 3));
});

test('the payment filter narrows the list', function () {
    $me = filterCustomer();
    $shop = filterShop();
    filterOrder($shop, $me, ['payment_status' => 'unpaid']);
    filterOrder($shop, $me, ['payment_status' => 'paid', 'amount_paid' => 180]);

    $this->actingAs($me)
        ->get(route('user.orders.index', ['payment' => 'paid']))
        ->assertInertia(fn (Assert $page) => $page
            ->where('orders.total', 1)
            ->where('orders.data.0.payment_status', 'paid'));
});

test('search matches the order number or the shop name', function () {
    $me = filterCustomer();
    $bubbles = filterShop('Bubbles Laundry');
    $fresh = filterShop('Fresh Press');
    filterOrder($bubbles, $me, ['order_number' => 'ONL-AAA111']);
    filterOrder($fresh, $me, ['order_number' => 'ONL-BBB222']);

    $this->actingAs($me)
        ->get(route('user.orders.index', ['search' => 'bubbles']))
        ->assertInertia(fn (Assert $page) => $page
            ->where('orders.total', 1)
            ->where('orders.data.0.order_number', 'ONL-AAA111'));

    $this->actingAs($me)
        ->get(route('user.orders.index', ['search' => 'bbb2']))
        ->assertInertia(fn (Assert $page) => $page
            ->where('orders.total', 1)
            ->where('orders.data.0.order_number', 'ONL-BBB222'));
});

test('filters combine, and the counts ignore the other filters', function () {
    $me = filterCustomer();
    $shop = filterShop();
    filterOrder($shop, $me, ['status' => 'pending', 'payment_status' => 'unpaid']);
    filterOrder($shop, $me, ['status' => 'pending', 'payment_status' => 'paid']);
    filterOrder($shop, $me, ['status' => 'completed', 'payment_status' => 'paid']);

    $this->actingAs($me)
        ->get(route('user.orders.index', ['status' => 'pending', 'payment' => 'paid']))
        ->assertInertia(fn (Assert $page) => $page
            ->where('orders.total', 1)
            ->where('counts.all', 3)
            ->where('counts.pending', 2));
});

test('search wildcards are treated as plain text', function () {
    $me = filterCustomer();
    filterOrder(filterShop(), $me);

    $this->actingAs($me)
        ->get(route('user.orders.index', ['search' => '%']))
        ->assertInertia(fn (Assert $page) => $page->where('orders.total', 0));
});

test('other customers\' orders never appear, whatever the filter', function () {
    $me = filterCustomer();
    $other = filterCustomer();
    filterOrder(filterShop(), $other, ['order_number' => 'ONL-THEIRS1']);

    $this->actingAs($me)
        ->get(route('user.orders.index', ['search' => 'THEIRS1']))
        ->assertInertia(fn (Assert $page) => $page->where('orders.total', 0)->where('counts.all', 0));
});

test('invalid filter values are rejected', function () {
    $this->actingAs(filterCustomer())
        ->get(route('user.orders.index', ['status' => 'shipped', 'payment' => 'maybe']))
        ->assertSessionHasErrors(['status', 'payment']);
});

test('the pagination keeps the filters', function () {
    $me = filterCustomer();
    $shop = filterShop();
    foreach (range(1, 17) as $i) {
        filterOrder($shop, $me, ['status' => 'pending']);
    }

    $this->actingAs($me)
        ->get(route('user.orders.index', ['status' => 'pending', 'page' => 2]))
        ->assertInertia(fn (Assert $page) => $page
            ->where('orders.current_page', 2)
            ->where('orders.last_page', 2)
            ->has('orders.data', 2));
});
