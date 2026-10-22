<?php

use App\Enums\AccountType;
use App\Models\Delivery;
use App\Models\OrderRating;
use App\Models\Shop;
use App\Models\ShopOrder;
use App\Models\ShopService;
use App\Models\User;

function ratingShop(array $overrides = []): Shop
{
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);

    return Shop::create(array_merge([
        'owner_id' => $owner->id,
        'shop_name' => 'Rated Laundry '.$owner->id,
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Poblacion',
        'postal_code' => '4103',
        'status' => 'active',
        'setup_completed_at' => now(),
    ], $overrides));
}

function ratingCustomer(): User
{
    return User::factory()->create(['role' => AccountType::Customer->value]);
}

/** A completed online order that is ready to be rated unless the overrides say otherwise. */
function ratingOrder(Shop $shop, User $customer, array $overrides = []): ShopOrder
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
        'pickup_type' => 'walk_in',
        'pricing_model' => 'per_kg',
        'price_per_kg' => 60,
        'estimated_weight_kg' => 3,
        'actual_weight_kg' => 3,
        'total_amount' => 180,
        'payment_status' => 'paid',
        'amount_paid' => 180,
        'status' => 'completed',
        'completed_at' => now()->subDay(),
    ], $overrides));
}

function rate(ShopOrder $order, array $payload = ['rating' => 5])
{
    return test()->post("/user/orders/{$order->payment_public_id}/rating", $payload);
}

test('a customer rates a completed order', function () {
    $customer = ratingCustomer();
    $order = ratingOrder($shop = ratingShop(), $customer);

    $this->actingAs($customer);
    rate($order, ['rating' => 4, 'comment' => 'Fast and clean'])->assertSessionHasNoErrors();

    expect(OrderRating::first())
        ->shop_order_id->toBe($order->id)
        ->shop_id->toBe($shop->id)
        ->user_id->toBe($customer->id)
        ->rating->toBe(4)
        ->comment->toBe('Fast and clean');
});

test('the comment is optional and blank comments are stored as nothing', function () {
    $customer = ratingCustomer();
    $first = ratingOrder($shop = ratingShop(), $customer);
    $second = ratingOrder($shop, $customer);

    $this->actingAs($customer);
    rate($first, ['rating' => 5])->assertSessionHasNoErrors();
    rate($second, ['rating' => 3, 'comment' => '   '])->assertSessionHasNoErrors();

    expect(OrderRating::pluck('comment')->all())->toBe([null, null]);
});

test('an order can be rated only once and the first rating is kept', function () {
    $customer = ratingCustomer();
    $order = ratingOrder(ratingShop(), $customer);

    $this->actingAs($customer);
    rate($order, ['rating' => 5]);
    rate($order, ['rating' => 1, 'comment' => 'changed my mind'])
        ->assertSessionHas('toast.type', 'error');

    expect(OrderRating::count())->toBe(1)
        ->and(OrderRating::first()->rating)->toBe(5);
});

test('only completed orders can be rated', function (string $status) {
    $customer = ratingCustomer();
    $order = ratingOrder(ratingShop(), $customer, ['status' => $status, 'completed_at' => null]);

    $this->actingAs($customer);
    rate($order)->assertSessionHas('toast.type', 'error');

    expect(OrderRating::count())->toBe(0);
})->with(['pending', 'in_progress', 'cancelled']);

test('a delivered order can be rated but one still on the road cannot', function () {
    $customer = ratingCustomer();
    $shop = ratingShop();
    $onTheRoad = ratingOrder($shop, $customer);
    $delivered = ratingOrder($shop, $customer);
    Delivery::create(['shop_id' => $shop->id, 'shop_order_id' => $onTheRoad->id, 'type' => 'delivery', 'customer_name' => 'A', 'status' => 'picked_up']);
    Delivery::create(['shop_id' => $shop->id, 'shop_order_id' => $delivered->id, 'type' => 'delivery', 'customer_name' => 'B', 'status' => 'delivered', 'delivered_at' => now()]);

    $this->actingAs($customer);
    rate($onTheRoad)->assertSessionHas('toast.type', 'error');
    rate($delivered)->assertSessionHasNoErrors();

    expect(OrderRating::pluck('shop_order_id')->all())->toBe([$delivered->id]);
});

test('a return delivery that is pending or failed blocks rating', function (string $status) {
    $customer = ratingCustomer();
    $shop = ratingShop();
    $order = ratingOrder($shop, $customer);
    Delivery::create(['shop_id' => $shop->id, 'shop_order_id' => $order->id, 'type' => 'delivery', 'customer_name' => 'A', 'status' => $status]);

    $this->actingAs($customer);
    rate($order)->assertSessionHas('toast.type', 'error');

    expect(OrderRating::count())->toBe(0);
})->with(['pending', 'failed']);

test('a deleted order cannot be rated', function () {
    $customer = ratingCustomer();
    $order = ratingOrder(ratingShop(), $customer);
    $order->delete();

    $this->actingAs($customer);
    rate($order)->assertNotFound();

    expect(OrderRating::count())->toBe(0);
});

test('rating requests are throttled', function () {
    $customer = ratingCustomer();
    $order = ratingOrder(ratingShop(), $customer);

    $this->actingAs($customer);
    foreach (range(1, 20) as $attempt) {
        rate($order, []);
    }

    rate($order, ['rating' => 5])->assertStatus(429);

    expect(OrderRating::count())->toBe(0);
});

test('a pickup run does not block rating once the return delivery is done', function () {
    $customer = ratingCustomer();
    $shop = ratingShop();
    $order = ratingOrder($shop, $customer, ['pickup_type' => 'pickup']);
    Delivery::create(['shop_id' => $shop->id, 'shop_order_id' => $order->id, 'type' => 'pickup', 'customer_name' => 'A', 'status' => 'delivered']);

    $this->actingAs($customer);
    rate($order)->assertSessionHasNoErrors();

    expect(OrderRating::count())->toBe(1);
});

test('the rating window closes 30 days after completion', function () {
    $customer = ratingCustomer();
    $shop = ratingShop();
    $recent = ratingOrder($shop, $customer, ['completed_at' => now()->subDays(29)]);
    $old = ratingOrder($shop, $customer, ['completed_at' => now()->subDays(31)]);

    $this->actingAs($customer);
    rate($recent)->assertSessionHasNoErrors();
    rate($old)->assertSessionHas('toast.type', 'error');

    expect(OrderRating::pluck('shop_order_id')->all())->toBe([$recent->id]);
});

test('the rating is validated', function (array $payload, string $field) {
    $customer = ratingCustomer();
    $order = ratingOrder(ratingShop(), $customer);

    $this->actingAs($customer);
    rate($order, $payload)->assertSessionHasErrors($field);

    expect(OrderRating::count())->toBe(0);
})->with([
    'missing stars' => [[], 'rating'],
    'zero stars' => [['rating' => 0], 'rating'],
    'six stars' => [['rating' => 6], 'rating'],
    'not a number' => [['rating' => 'great'], 'rating'],
    'half a star' => [['rating' => 4.5], 'rating'],
    'comment too long' => [['rating' => 5, 'comment' => str_repeat('a', 501)], 'comment'],
]);

test('a customer cannot rate someone elses order', function () {
    $order = ratingOrder(ratingShop(), ratingCustomer());

    $this->actingAs(ratingCustomer());
    rate($order)->assertNotFound();

    expect(OrderRating::count())->toBe(0);
});

test('guests and shop accounts cannot rate', function () {
    $customer = ratingCustomer();
    $shop = ratingShop();
    $order = ratingOrder($shop, $customer);

    rate($order)->assertRedirect('/login');

    $this->actingAs($shop->owner);
    expect(rate($order)->status())->toBeIn([302, 403]);

    expect(OrderRating::count())->toBe(0);
});

test('the order page says whether it can be rated and shows the rating afterwards', function () {
    $customer = ratingCustomer();
    $order = ratingOrder(ratingShop(), $customer);

    $this->actingAs($customer)
        ->get("/user/orders/{$order->payment_public_id}")
        ->assertInertia(fn ($page) => $page
            ->where('order.rating.can_rate', true)
            ->where('order.rating.rating', null));

    rate($order, ['rating' => 5, 'comment' => 'Great']);

    $this->get("/user/orders/{$order->payment_public_id}")
        ->assertInertia(fn ($page) => $page
            ->where('order.rating.can_rate', false)
            ->where('order.rating.rating.stars', 5)
            ->where('order.rating.rating.comment', 'Great'));
});

test('the dashboard offers the latest unrated order and stops once it is rated', function () {
    $customer = ratingCustomer();
    $shop = ratingShop();
    $pending = ratingOrder($shop, $customer, ['status' => 'pending', 'completed_at' => null]);
    $ready = ratingOrder($shop, $customer);

    $this->actingAs($customer)
        ->get('/user/dashboard')
        ->assertInertia(fn ($page) => $page
            ->where('ratingPrompt.payment_public_id', $ready->payment_public_id)
            ->where('ratingPrompt.shop_name', $shop->shop_name));

    rate($ready);

    $this->get('/user/dashboard')->assertInertia(fn ($page) => $page->where('ratingPrompt', null));

    expect($pending->fresh()->rating)->toBeNull();
});

test('customers see a shops average and count but never the comments', function () {
    $shop = ratingShop();
    $other = ratingShop();
    $customer = ratingCustomer();

    foreach ([5, 4, 3] as $stars) {
        $buyer = ratingCustomer();
        $order = ratingOrder($shop, $buyer);
        test()->actingAs($buyer);
        rate($order, ['rating' => $stars, 'comment' => 'zxqsecretcomment']);
    }

    $this->actingAs($customer)
        ->get('/user/shops')
        ->assertDontSee('zxqsecretcomment')
        ->assertInertia(fn ($page) => $page
            ->where('shops', fn ($shops) => collect($shops)->firstWhere('id', $shop->id)['rating_avg'] == 4
                && collect($shops)->firstWhere('id', $shop->id)['rating_count'] === 3
                && collect($shops)->firstWhere('id', $other->id)['rating_avg'] === null
                && collect($shops)->firstWhere('id', $other->id)['rating_count'] === 0));

    $this->get("/user/shops/{$shop->id}")
        ->assertDontSee('zxqsecretcomment')
        ->assertInertia(fn ($page) => $page
            ->where('shop.rating_avg', 4)
            ->where('shop.rating_count', 3));
});
