<?php

use App\Enums\AccountType;
use App\Http\Middleware\EnforceOwnerPlatformPermissions;
use App\Http\Middleware\EnsureCurrentBusinessAgreementAccepted;
use App\Models\Delivery;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Shop;
use App\Models\ShopOrder;
use App\Models\ShopService;
use App\Models\User;
use App\Services\LogisticsService;
use App\Services\OrderArchiveService;
use Illuminate\Validation\ValidationException;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->withoutMiddleware([
        EnsureCurrentBusinessAgreementAccepted::class,
        EnforceOwnerPlatformPermissions::class,
    ]);
});

/** A shop with a laundry service and a customer who uses it. */
function retailScenario(): array
{
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
    $customer = User::factory()->create(['role' => AccountType::Customer->value]);
    $shop = Shop::create([
        'owner_id' => $owner->id,
        'shop_name' => 'Retail Laundry '.$owner->id,
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Poblacion',
        'postal_code' => '4103',
        'status' => 'active',
        'location_mode' => 'single',
        'offers_pickup' => true,
        'offers_delivery' => false,
        'setup_completed_at' => now(),
    ]);
    $service = ShopService::create([
        'shop_id' => $shop->id,
        'service_name' => 'Wash and Fold',
        'is_active' => true,
        'pricing_model' => 'per_kg',
        'price_per_kg' => 60,
    ]);

    $customer->update(['referred_shop_id' => $shop->id]);

    return compact('owner', 'customer', 'shop', 'service');
}

/** A softener sold as a 1,000 ml bottle (stock in ml). */
function retailItem(Shop $shop, array $overrides = []): Inventory
{
    return Inventory::create(array_merge([
        'shop_id' => $shop->id,
        'name' => 'Softener stock',
        'sku' => strtoupper(uniqid('SKU')),
        'unit' => 'ml',
        'quantity' => 5000,
        'min_stock' => 1,
        'unit_price' => 0.1,
        'selling_price' => 0.15,
        'retail_enabled' => true,
        'retail_name' => 'Softener 1L bottle',
        'retail_pack_size' => 1000,
        'retail_price' => 150,
        'status' => 'active',
    ], $overrides));
}

function laundryPayload(Shop $shop, ShopService $service, array $overrides = []): array
{
    return array_merge([
        'shop_id' => $shop->id,
        'service_id' => $service->id,
        'customer_name' => 'Test Customer',
        'customer_phone' => '09171234567',
        'estimated_weight_kg' => 2,
        'pickup_type' => 'walk_in',
        'payment_method' => 'cash',
        'customer_agreement_version' => config('customer_service_agreement.version'),
        'customer_agreement_accepted' => true,
    ], $overrides);
}

function productsPayload(Shop $shop, array $items, array $overrides = []): array
{
    return array_merge([
        'shop_id' => $shop->id,
        'retail_items' => $items,
        'customer_name' => 'Test Customer',
        'customer_phone' => '09171234567',
    ], $overrides);
}

// ─── Shop page ──────────────────────────────────────────────────────────────

test('the shop page lists active retail products without stock or cost', function () {
    ['customer' => $customer, 'shop' => $shop] = retailScenario();
    $bottle = retailItem($shop);
    retailItem($shop, ['retail_name' => 'Low bottle', 'quantity' => 2500]);
    retailItem($shop, ['retail_name' => 'Empty bottle', 'quantity' => 400]);
    retailItem($shop, ['retail_name' => 'Not for sale', 'retail_enabled' => false]);
    retailItem($shop, ['retail_name' => 'Inactive bottle', 'status' => 'inactive']);
    retailItem(retailScenario()['shop'], ['retail_name' => 'Other shop bottle']);

    $this->actingAs($customer)
        ->get("/user/shops/{$shop->id}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('user/ShopDetail')
            ->has('retailProducts', 3)
            ->where('retailProducts', fn ($products) => collect($products)->pluck('name')->sort()->values()->all()
                === ['Empty bottle', 'Low bottle', 'Softener 1L bottle'])
            ->where('retailProducts', function ($products) use ($bottle) {
                $products = collect($products)->keyBy('name');
                $first = $products['Softener 1L bottle'];

                return $first['id'] === $bottle->id
                    && $first['pack_size'] === 1000
                    && (float) $first['price'] === 150.0
                    && $first['in_stock'] === true
                    && $first['max_count'] === 5
                    && $products['Low bottle']['max_count'] === 2
                    && $products['Empty bottle']['in_stock'] === false
                    && ! array_key_exists('quantity', $first)
                    && ! array_key_exists('unit_price', $first);
            }));
});

// ─── Products with a laundry order ──────────────────────────────────────────

test('a laundry order can include retail products, priced by the pack and taken from stock', function () {
    ['customer' => $customer, 'shop' => $shop, 'service' => $service] = retailScenario();
    $bottle = retailItem($shop);

    $this->actingAs($customer)
        ->post(route('user.orders.store'), laundryPayload($shop, $service, [
            'retail_items' => [['id' => $bottle->id, 'count' => 2]],
        ]))
        ->assertSessionHasNoErrors();

    $order = ShopOrder::where('user_id', $customer->id)->with('supplies')->firstOrFail();
    $line = $order->supplies->first();

    expect($order->order_type)->toBe('laundry')
        ->and((float) $order->additional_charges)->toBe(300.0)
        ->and((float) $order->total_amount)->toBe(420.0)
        ->and($line->pivot->label)->toBe('Softener 1L bottle × 2')
        ->and((float) $line->pivot->quantity_used)->toBe(2000.0)
        ->and($bottle->fresh()->quantity)->toBe(3000);
});

test('the estimate includes the retail products', function () {
    ['customer' => $customer, 'shop' => $shop, 'service' => $service] = retailScenario();
    $bottle = retailItem($shop);

    $this->actingAs($customer)
        ->getJson('/user/orders/estimate?'.http_build_query([
            'shop_id' => $shop->id,
            'service_id' => $service->id,
            'estimated_weight_kg' => 2,
            'retail_items' => [['id' => $bottle->id, 'count' => 1]],
        ]))
        ->assertOk()
        ->assertJsonPath('additional_charges', 150)
        ->assertJsonPath('supplies.0.name', 'Softener 1L bottle × 1')
        ->assertJsonPath('total', 270);
});

test('a product from another shop, or no longer sold, is rejected', function () {
    ['customer' => $customer, 'shop' => $shop, 'service' => $service] = retailScenario();
    $foreign = retailItem(retailScenario()['shop']);
    $notForSale = retailItem($shop, ['retail_enabled' => false]);

    foreach ([$foreign, $notForSale] as $item) {
        $this->actingAs($customer)
            ->post(route('user.orders.store'), laundryPayload($shop, $service, [
                'retail_items' => [['id' => $item->id, 'count' => 1]],
            ]))
            ->assertSessionHasErrors('retail_items');
    }

    expect(ShopOrder::count())->toBe(0)
        ->and($foreign->fresh()->quantity)->toBe(5000);
});

test('an order cannot take more products than the shop has in stock', function () {
    ['customer' => $customer, 'shop' => $shop, 'service' => $service] = retailScenario();
    $bottle = retailItem($shop, ['quantity' => 1500]);

    $this->actingAs($customer)
        ->post(route('user.orders.store'), laundryPayload($shop, $service, [
            'retail_items' => [['id' => $bottle->id, 'count' => 2]],
        ]))
        ->assertSessionHasErrors('retail_items');

    expect(ShopOrder::count())->toBe(0)
        ->and($bottle->fresh()->quantity)->toBe(1500);
});

test('an item picked as an add-on and as a product becomes one line with the combined stock check', function () {
    ['customer' => $customer, 'shop' => $shop, 'service' => $service] = retailScenario();
    $softener = retailItem($shop, [
        'quantity' => 1040,
        'addon_enabled' => true,
        'addon_name' => 'Extra softener',
        'addon_quantity' => 30,
        'addon_price' => 15,
    ]);

    // 30 ml + 1,000 ml fits the 1,040 ml in stock.
    $this->actingAs($customer)
        ->post(route('user.orders.store'), laundryPayload($shop, $service, [
            'additional_items' => [['id' => $softener->id, 'count' => 1]],
            'retail_items' => [['id' => $softener->id, 'count' => 1]],
        ]))
        ->assertSessionHasNoErrors();

    $order = ShopOrder::where('user_id', $customer->id)->with('supplies')->firstOrFail();
    $line = $order->supplies->sole();

    expect($line->pivot->label)->toBe('Extra softener + Softener 1L bottle × 1')
        ->and((float) $line->pivot->quantity_used)->toBe(1030.0)
        ->and(round((float) $line->pivot->unit_price * 1030, 2))->toBe(165.0)
        ->and((float) $order->additional_charges)->toBe(165.0)
        ->and($softener->fresh()->quantity)->toBe(10);

    // A second order of the same would need 1,030 ml again, but only 10 ml is left.
    $this->actingAs($customer)
        ->post(route('user.orders.store'), laundryPayload($shop, $service, [
            'additional_items' => [['id' => $softener->id, 'count' => 1]],
            'retail_items' => [['id' => $softener->id, 'count' => 1]],
        ]))
        ->assertSessionHasErrors('retail_items');
});

test('a price that is not a whole centavo per unit still adds up on the order', function () {
    ['customer' => $customer, 'shop' => $shop, 'service' => $service] = retailScenario();
    $bottle = retailItem($shop, ['retail_price' => 149]);

    $this->actingAs($customer)
        ->post(route('user.orders.store'), laundryPayload($shop, $service, [
            'retail_items' => [['id' => $bottle->id, 'count' => 1]],
        ]))
        ->assertSessionHasNoErrors();

    $order = ShopOrder::where('user_id', $customer->id)->firstOrFail();

    $this->actingAs($customer)
        ->get("/user/orders/{$order->payment_public_id}")
        ->assertInertia(fn (Assert $page) => $page
            ->where('order.billing.supplies.0.amount', 149)
            ->where('order.billing.other_charges', 0));
});

// ─── Products on their own ──────────────────────────────────────────────────

test('a customer can buy products on their own and collect them at the shop', function () {
    ['customer' => $customer, 'shop' => $shop, 'owner' => $owner] = retailScenario();
    $bottle = retailItem($shop);

    $response = $this->actingAs($customer)
        ->post('/user/orders/products', productsPayload($shop, [['id' => $bottle->id, 'count' => 2]]))
        ->assertSessionHasNoErrors();

    $order = ShopOrder::where('user_id', $customer->id)->firstOrFail();
    $response->assertRedirect(route('user.orders.show', $order->payment_public_id));

    expect($order->order_type)->toBe('products')
        ->and($order->isProductOrder())->toBeTrue()
        ->and($order->service_id)->toBeNull()
        ->and($order->pickup_type)->toBe('walk_in')
        ->and(str_starts_with($order->order_number, 'PRD-'))->toBeTrue()
        ->and((float) $order->total_amount)->toBe(300.0)
        ->and($order->priceIsConfirmed())->toBeTrue()
        ->and(Delivery::where('shop_order_id', $order->id)->exists())->toBeFalse()
        ->and($bottle->fresh()->quantity)->toBe(3000)
        ->and($owner->notifications()->count())->toBe(1);

    $this->actingAs($customer)
        ->get("/user/orders/{$order->payment_public_id}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('user/OrderDetail')
            ->where('order.order_type', 'products')
            ->where('order.service_name', 'Products')
            ->where('order.payment_ready', true)
            ->where('order.billing.service', null)
            ->where('order.billing.charges_label', 'Products')
            ->where('order.billing.supplies.0.name', 'Softener 1L bottle × 2'));
});

test('the products estimate prices the picked products', function () {
    ['customer' => $customer, 'shop' => $shop] = retailScenario();
    $bottle = retailItem($shop);

    $this->actingAs($customer)
        ->getJson('/user/orders/products/estimate?'.http_build_query([
            'shop_id' => $shop->id,
            'retail_items' => [['id' => $bottle->id, 'count' => 3]],
        ]))
        ->assertOk()
        ->assertJsonPath('service', null)
        ->assertJsonPath('additional_charges', 450)
        ->assertJsonPath('total', 450);
});

test('a products order needs at least one product the shop sells', function () {
    ['customer' => $customer, 'shop' => $shop] = retailScenario();
    $foreign = retailItem(retailScenario()['shop']);

    $this->actingAs($customer)
        ->post('/user/orders/products', productsPayload($shop, []))
        ->assertSessionHasErrors('retail_items');

    $this->actingAs($customer)
        ->post('/user/orders/products', productsPayload($shop, [['id' => $foreign->id, 'count' => 1]]))
        ->assertSessionHasErrors('retail_items');

    expect(ShopOrder::count())->toBe(0);
});

test('cancelling a products order puts the products back in stock, only for its own customer', function () {
    ['customer' => $customer, 'shop' => $shop] = retailScenario();
    $bottle = retailItem($shop);
    $this->actingAs($customer)->post('/user/orders/products', productsPayload($shop, [['id' => $bottle->id, 'count' => 2]]));
    $order = ShopOrder::where('user_id', $customer->id)->firstOrFail();

    $stranger = User::factory()->create(['role' => AccountType::Customer->value]);
    $this->actingAs($stranger)
        ->post("/user/orders/{$order->payment_public_id}/cancel")
        ->assertNotFound();

    $this->actingAs($customer)
        ->post("/user/orders/{$order->payment_public_id}/cancel")
        ->assertRedirect(route('user.orders.index'));

    expect($order->fresh()->status)->toBe('cancelled')
        ->and($bottle->fresh()->quantity)->toBe(5000)
        ->and(InventoryMovement::where('inventory_id', $bottle->id)->where('type', 'return')->value('quantity'))->toBe(2000);
});

test('the shop marks a products order ready, hands it over once paid, and cannot deliver it', function () {
    ['customer' => $customer, 'shop' => $shop, 'owner' => $owner] = retailScenario();
    $bottle = retailItem($shop);
    $this->actingAs($customer)->post('/user/orders/products', productsPayload($shop, [['id' => $bottle->id, 'count' => 1]]));
    $order = ShopOrder::where('user_id', $customer->id)->firstOrFail();
    $url = "/shop/operations/orders/{$order->payment_public_id}";

    // Ready straight from pending: there is nothing to weigh.
    $this->actingAs($owner)->patch("{$url}/status", ['status' => 'completed'])->assertSessionHasNoErrors();
    expect($order->fresh()->status)->toBe('completed');

    // Not paid yet, so it cannot be handed over.
    $this->actingAs($owner)->patch("{$url}/status", ['status' => 'picked_up'])->assertSessionHasErrors('status');

    $order->update(['payment_status' => 'paid', 'amount_paid' => $order->total_amount]);
    $this->actingAs($owner)->patch("{$url}/status", ['status' => 'picked_up'])->assertSessionHasNoErrors();
    expect($order->fresh()->status)->toBe('picked_up');

    // The edit page has nothing for a products order and sends staff back to the order.
    $this->actingAs($owner)->get("{$url}/edit")->assertRedirect($url);
    $this->actingAs($owner);
    expect(fn () => app(LogisticsService::class)->sendOrderForDelivery($owner, $order->fresh(), 'Somewhere in Imus'))
        ->toThrow(ValidationException::class, 'collected at the shop');
});

test('a shop cancelling a products order returns stock once, even when it is archived later', function () {
    ['customer' => $customer, 'shop' => $shop, 'owner' => $owner] = retailScenario();
    $bottle = retailItem($shop);
    $this->actingAs($customer)->post('/user/orders/products', productsPayload($shop, [['id' => $bottle->id, 'count' => 2]]));
    $order = ShopOrder::where('user_id', $customer->id)->firstOrFail();
    $url = "/shop/operations/orders/{$order->payment_public_id}";

    $this->actingAs($owner)->patch("{$url}/status", ['status' => 'cancelled'])->assertSessionHasNoErrors();
    expect($bottle->fresh()->quantity)->toBe(5000);

    app(OrderArchiveService::class)->archive($order->fresh(), $owner->id);
    expect($bottle->fresh()->quantity)->toBe(5000);

    app(OrderArchiveService::class)->restore($order->payment_public_id, $shop->id, $owner->id);
    expect($bottle->fresh()->quantity)->toBe(5000)
        ->and($order->fresh()->status)->toBe('cancelled');
});

test('reopening a cancelled products order takes the products again', function () {
    ['customer' => $customer, 'shop' => $shop, 'owner' => $owner] = retailScenario();
    $bottle = retailItem($shop);
    $this->actingAs($customer)->post('/user/orders/products', productsPayload($shop, [['id' => $bottle->id, 'count' => 2]]));
    $order = ShopOrder::where('user_id', $customer->id)->firstOrFail();
    $url = "/shop/operations/orders/{$order->payment_public_id}";

    $this->actingAs($owner)->patch("{$url}/status", ['status' => 'cancelled']);
    $this->actingAs($owner)->patch("{$url}/status", ['status' => 'in_progress'])->assertSessionHasNoErrors();

    expect($order->fresh()->status)->toBe('in_progress')
        ->and($bottle->fresh()->quantity)->toBe(3000);
});

test('another shop cannot see or change a products order', function () {
    ['customer' => $customer, 'shop' => $shop] = retailScenario();
    $bottle = retailItem($shop);
    $this->actingAs($customer)->post('/user/orders/products', productsPayload($shop, [['id' => $bottle->id, 'count' => 1]]));
    $order = ShopOrder::where('user_id', $customer->id)->firstOrFail();
    $otherOwner = retailScenario()['owner'];

    $this->actingAs($otherOwner)->get("/shop/operations/orders/{$order->payment_public_id}")->assertNotFound();
    $this->actingAs($otherOwner)
        ->patch("/shop/operations/orders/{$order->payment_public_id}/status", ['status' => 'cancelled'])
        ->assertNotFound();

    expect($order->fresh()->status)->toBe('pending');
});

test('a products order cannot be sent for delivery by the customer', function () {
    ['customer' => $customer, 'shop' => $shop] = retailScenario();
    $bottle = retailItem($shop);
    $this->actingAs($customer)->post('/user/orders/products', productsPayload($shop, [['id' => $bottle->id, 'count' => 1]]));
    $order = ShopOrder::where('user_id', $customer->id)->firstOrFail();
    $order->update(['status' => 'completed', 'payment_status' => 'paid', 'amount_paid' => $order->total_amount]);

    $service = app(\App\Services\DeliveryRequestService::class);

    expect(fn () => $service->request($customer, $order->payment_public_id, [
        'delivery_address' => 'Somewhere in Imus',
        'destination_lat' => 14.4,
        'destination_lng' => 120.9,
    ]))
        ->toThrow(\App\Exceptions\DeliveryRequestException::class, 'collected at the shop');
});

test('the shop opens a products order with its products on the receipt', function () {
    ['customer' => $customer, 'shop' => $shop, 'owner' => $owner] = retailScenario();
    $bottle = retailItem($shop);
    $this->actingAs($customer)->post('/user/orders/products', productsPayload($shop, [['id' => $bottle->id, 'count' => 2]]));
    $order = ShopOrder::where('user_id', $customer->id)->firstOrFail();

    $this->actingAs($owner)
        ->get("/shop/operations/orders/{$order->payment_public_id}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('shop/operations/orders/Show')
            ->where('shopOrder.order_type', 'products')
            ->where('receipt.items.0.label', 'Softener 1L bottle × 2')
            ->where('receipt.items.0.amount', 300));
});

test('archiving a pending products order returns its products and restoring takes them again', function () {
    ['customer' => $customer, 'shop' => $shop, 'owner' => $owner] = retailScenario();
    $bottle = retailItem($shop);
    $this->actingAs($customer)->post('/user/orders/products', productsPayload($shop, [['id' => $bottle->id, 'count' => 2]]));
    $order = ShopOrder::where('user_id', $customer->id)->firstOrFail();

    app(OrderArchiveService::class)->archive($order, $owner->id);
    expect($bottle->fresh()->quantity)->toBe(5000);

    app(OrderArchiveService::class)->restore($order->payment_public_id, $shop->id, $owner->id);
    expect($bottle->fresh()->quantity)->toBe(3000);
});

test('reopening a cancelled products order fails and changes nothing when stock ran out', function () {
    ['customer' => $customer, 'shop' => $shop, 'owner' => $owner] = retailScenario();
    $bottle = retailItem($shop);
    $this->actingAs($customer)->post('/user/orders/products', productsPayload($shop, [['id' => $bottle->id, 'count' => 2]]));
    $order = ShopOrder::where('user_id', $customer->id)->firstOrFail();
    $url = "/shop/operations/orders/{$order->payment_public_id}";

    $this->actingAs($owner)->patch("{$url}/status", ['status' => 'cancelled']);
    $bottle->update(['quantity' => 1000]);

    $this->actingAs($owner)->patch("{$url}/status", ['status' => 'in_progress'])->assertSessionHasErrors('supplies');

    expect($order->fresh()->status)->toBe('cancelled')
        ->and($bottle->fresh()->quantity)->toBe(1000);
});

test('a pack too large for one order line is rejected with a message', function () {
    ['customer' => $customer, 'shop' => $shop] = retailScenario();
    $drum = retailItem($shop, ['quantity' => 5000000, 'retail_pack_size' => 200000, 'retail_price' => 5000]);

    $this->actingAs($customer)
        ->post('/user/orders/products', productsPayload($shop, [['id' => $drum->id, 'count' => 5]]))
        ->assertSessionHasErrors('retail_items');

    expect(ShopOrder::count())->toBe(0);
});

// ─── Cancelling a laundry order with add-ons or products ────────────────────

test('a customer cancelling a laundry order puts its products back in stock, once', function () {
    ['customer' => $customer, 'shop' => $shop, 'service' => $service, 'owner' => $owner] = retailScenario();
    $bottle = retailItem($shop);
    $this->actingAs($customer)->post(route('user.orders.store'), laundryPayload($shop, $service, [
        'retail_items' => [['id' => $bottle->id, 'count' => 2]],
    ]));
    $order = ShopOrder::where('user_id', $customer->id)->firstOrFail();
    expect($bottle->fresh()->quantity)->toBe(3000);

    $this->actingAs($customer)
        ->post("/user/orders/{$order->payment_public_id}/cancel")
        ->assertRedirect(route('user.orders.index'));

    expect($order->fresh()->status)->toBe('cancelled')
        ->and($order->fresh()->supplies_returned_at)->not->toBeNull()
        ->and($bottle->fresh()->quantity)->toBe(5000)
        ->and($owner->notifications()->where('data->type', 'cancelled')->count())->toBe(1);

    // Archiving and restoring the cancelled order leaves the stock alone.
    app(OrderArchiveService::class)->archive($order->fresh(), $owner->id);
    expect($bottle->fresh()->quantity)->toBe(5000);
    app(OrderArchiveService::class)->restore($order->payment_public_id, $shop->id, $owner->id);
    expect($bottle->fresh()->quantity)->toBe(5000);
});

test('the shop cancelling then reopening a laundry order returns and retakes its products', function () {
    ['customer' => $customer, 'shop' => $shop, 'service' => $service, 'owner' => $owner] = retailScenario();
    $bottle = retailItem($shop);
    $this->actingAs($customer)->post(route('user.orders.store'), laundryPayload($shop, $service, [
        'retail_items' => [['id' => $bottle->id, 'count' => 2]],
    ]));
    $order = ShopOrder::where('user_id', $customer->id)->firstOrFail();
    $url = "/shop/operations/orders/{$order->payment_public_id}";

    $this->actingAs($owner)->patch("{$url}/status", ['status' => 'cancelled'])->assertSessionHasNoErrors();
    expect($bottle->fresh()->quantity)->toBe(5000);

    $this->actingAs($owner)->patch("{$url}/status", ['status' => 'pending'])->assertSessionHasNoErrors();
    expect($order->fresh()->status)->toBe('pending')
        ->and($order->fresh()->supplies_returned_at)->toBeNull()
        ->and($bottle->fresh()->quantity)->toBe(3000);
});

test('an order cancelled before stock was returned on cancel still returns it when archived', function () {
    ['customer' => $customer, 'shop' => $shop, 'service' => $service, 'owner' => $owner] = retailScenario();
    $bottle = retailItem($shop);
    $this->actingAs($customer)->post(route('user.orders.store'), laundryPayload($shop, $service, [
        'retail_items' => [['id' => $bottle->id, 'count' => 2]],
    ]));
    $order = ShopOrder::where('user_id', $customer->id)->firstOrFail();

    // How a cancelled order looked before: cancelled, with its products still out of stock.
    ShopOrder::whereKey($order->id)->update(['status' => 'cancelled']);

    app(OrderArchiveService::class)->archive($order->fresh(), $owner->id);

    expect($bottle->fresh()->quantity)->toBe(5000);
});

test('service performance lists products orders as retail products', function () {
    ['customer' => $customer, 'shop' => $shop] = retailScenario();
    $bottle = retailItem($shop);
    $this->actingAs($customer)->post('/user/orders/products', productsPayload($shop, [['id' => $bottle->id, 'count' => 2]]));

    $rows = app(\App\Repositories\ShopAnalyticsRepository::class)
        ->servicePerformance($shop->id, now()->subDay(), now()->addDay());

    expect($rows->pluck('name')->all())->toBe(['Retail products'])
        ->and($rows->first()['revenue'])->toBe(300.0);
});
