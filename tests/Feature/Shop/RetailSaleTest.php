<?php

use App\Enums\AccountType;
use App\Http\Middleware\EnforceOwnerPlatformPermissions;
use App\Http\Middleware\EnsureCurrentBusinessAgreementAccepted;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\Shop;
use App\Models\ShopOrder;
use App\Models\User;
use App\Repositories\ShopOrderRepository;

beforeEach(function () {
    $this->withoutMiddleware([
        EnsureCurrentBusinessAgreementAccepted::class,
        EnforceOwnerPlatformPermissions::class,
    ]);
});

/** A shop with an active plan and one retail product (a ₱150 bottle, 1,000 ml, 3 bottles on the shelf). */
function retailShop(): array
{
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
    $shop = Shop::create([
        'owner_id' => $owner->id,
        'shop_name' => 'Retail Laundry '.$owner->id,
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
    $product = Inventory::create([
        'shop_id' => $shop->id, 'name' => 'Fabric softener', 'unit' => 'ml', 'quantity' => 3000,
        'min_stock' => 1, 'unit_price' => 80, 'status' => 'active',
        'retail_enabled' => true, 'retail_name' => 'Softener 1L', 'retail_pack_size' => 1000, 'retail_price' => 150,
    ]);

    return [$owner->fresh(), $shop, $product];
}

it('records a counter sale paid in full, takes the stock and keeps it out of the orders list', function () {
    [$owner, $shop, $product] = retailShop();

    $this->actingAs($owner)
        ->post('/shop/operations/retail', [
            'retail_items' => [['id' => $product->id, 'count' => 2]],
            'payment_method' => 'cash',
            'amount_tendered' => 500,
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $sale = ShopOrder::where('shop_id', $shop->id)->firstOrFail();

    expect($sale->order_type)->toBe('products')
        ->and($sale->order_source)->toBe('shop')
        ->and($sale->status)->toBe('picked_up')
        ->and($sale->payment_status)->toBe('paid')
        ->and((float) $sale->total_amount)->toBe(300.0)
        ->and($sale->customer_name)->toBe('Walk-in customer')
        ->and((float) $product->fresh()->quantity)->toBe(1000.0);

    // The orders list is laundry only; the sale shows on the Retail Products list.
    $repository = app(ShopOrderRepository::class);
    expect($repository->paginateByShop($shop->id)->total())->toBe(0)
        ->and($repository->paginateByShop($shop->id, 15, ['type' => 'products'])->total())->toBe(1);
});

it('refuses a sale when there is not enough stock or the cash is short', function () {
    [$owner, $shop, $product] = retailShop();

    $this->actingAs($owner)
        ->post('/shop/operations/retail', [
            'retail_items' => [['id' => $product->id, 'count' => 4]],
            'payment_method' => 'cash',
        ])
        ->assertSessionHasErrors('retail_items');

    $this->actingAs($owner)
        ->post('/shop/operations/retail', [
            'retail_items' => [['id' => $product->id, 'count' => 1]],
            'payment_method' => 'cash',
            'amount_tendered' => 100,
        ])
        ->assertSessionHasErrors('amount_tendered');

    expect(ShopOrder::count())->toBe(0)
        ->and((float) $product->fresh()->quantity)->toBe(3000.0);
});

it('shows the retail products page with counter and online sales', function () {
    [$owner, , $product] = retailShop();

    $this->actingAs($owner)->post('/shop/operations/retail', [
        'retail_items' => [['id' => $product->id, 'count' => 1]],
        'payment_method' => 'gcash',
    ])->assertRedirect();

    $this->actingAs($owner)
        ->get('/shop/operations/retail')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/operations/retail/Index')
            ->where('sales.total', 1)
            ->where('summary.sales_today', 1));

    $this->actingAs($owner)
        ->get('/shop/operations/retail/create')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/operations/retail/Create')
            ->has('products', 1));
});

it('does not sell another shops product', function () {
    [$owner] = retailShop();
    [, , $foreignProduct] = retailShop();

    $this->actingAs($owner)
        ->post('/shop/operations/retail', [
            'retail_items' => [['id' => $foreignProduct->id, 'count' => 1]],
            'payment_method' => 'cash',
        ])
        ->assertSessionHasErrors('retail_items');

    expect(ShopOrder::count())->toBe(0);
});

it('adds retail products to a laundry order made at the counter', function () {
    [$owner, $shop, $product] = retailShop();
    $service = App\Models\ShopService::create([
        'shop_id' => $shop->id, 'service_name' => 'Wash and Fold', 'is_active' => true,
        'pricing_model' => 'per_kg', 'price_per_kg' => 60,
    ]);

    $order = app(App\Services\ShopOrderService::class)->createOrder([
        'shop_id' => $shop->id, 'customer_name' => 'Henry', 'customer_phone' => '0917 555 1234',
        'service_id' => $service->id, 'pickup_type' => 'walk_in', 'pricing_model' => 'per_kg',
        'actual_weight_kg' => 5, 'price_per_kg' => 60, 'additional_charges' => 0,
        'discount_amount' => 0, 'total_amount' => 300,
        // The browser sends only the packs; the server prices them.
        'retail_items' => [['id' => $product->id, 'count' => 2]],
        'customer_agreement_version' => '1.0', 'customer_agreement_accepted' => true,
    ], $owner);

    expect($order->order_type)->toBe('laundry')
        ->and((float) $order->additional_charges)->toBe(300.0)
        ->and((float) $order->total_amount)->toBe(600.0)
        ->and((float) $product->fresh()->quantity)->toBe(1000.0)
        ->and($order->supplies()->count())->toBe(1);
});

it('combines a supply and a retail product of the same item into one row on a counter order', function () {
    [$owner, $shop, $product] = retailShop();
    $product->update(['selling_price' => 0.5]);
    $service = App\Models\ShopService::create([
        'shop_id' => $shop->id, 'service_name' => 'Wash and Fold', 'is_active' => true,
        'pricing_model' => 'per_kg', 'price_per_kg' => 60,
    ]);

    $order = app(App\Services\ShopOrderService::class)->createOrder([
        'shop_id' => $shop->id, 'customer_name' => 'Henry', 'customer_phone' => '0917 555 1234',
        'service_id' => $service->id, 'pickup_type' => 'walk_in', 'pricing_model' => 'per_kg',
        'actual_weight_kg' => 5, 'price_per_kg' => 60, 'additional_charges' => 20,
        'discount_amount' => 0, 'total_amount' => 320,
        'supplies' => [['inventory_id' => $product->id, 'quantity_used' => 40, 'unit' => 'ml']],
        'retail_items' => [['id' => $product->id, 'count' => 1]],
        'customer_agreement_version' => '1.0', 'customer_agreement_accepted' => true,
    ], $owner);

    $row = $order->supplies()->first();

    expect($order->supplies()->count())->toBe(1)
        ->and((float) $row->pivot->quantity_used)->toBe(1040.0)
        ->and((float) $product->fresh()->quantity)->toBe(1960.0);
});
