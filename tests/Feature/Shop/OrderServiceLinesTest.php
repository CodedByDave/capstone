<?php

use App\Enums\AccountType;
use App\Http\Middleware\EnforceOwnerPlatformPermissions;
use App\Http\Middleware\EnsureCurrentBusinessAgreementAccepted;
use App\Models\Shop;
use App\Models\ShopOrder;
use App\Models\ShopService;
use App\Models\User;
use App\Services\OrderBillingBreakdownService;
use App\Services\ShopOrderService;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->withoutMiddleware([
        EnsureCurrentBusinessAgreementAccepted::class,
        EnforceOwnerPlatformPermissions::class,
    ]);
});

function linesShop(): array
{
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
    $shop = Shop::create([
        'owner_id' => $owner->id,
        'shop_name' => 'Lines Laundry '.$owner->id,
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Poblacion',
        'postal_code' => '4103',
        'status' => 'active',
        'location_mode' => 'single',
        'setup_completed_at' => now(),
    ]);
    $wash = ShopService::create([
        'shop_id' => $shop->id,
        'service_name' => 'Wash and Fold',
        'is_active' => true,
        'pricing_model' => 'per_kg',
        'price_per_kg' => 60,
    ]);
    $comforter = ShopService::create([
        'shop_id' => $shop->id,
        'service_name' => 'Comforter',
        'is_active' => true,
        'pricing_model' => 'per_piece',
        'price_per_piece' => 250,
    ]);

    return compact('owner', 'shop', 'wash', 'comforter');
}

function linesOrderData(array $ctx, array $extra = []): array
{
    return array_merge([
        'shop_id' => $ctx['shop']->id,
        'customer_name' => 'Maria Cruz',
        'service_id' => $ctx['wash']->id,
        'pickup_type' => 'walk_in',
        'pricing_model' => 'per_kg',
        'actual_weight_kg' => 5,
        'price_per_kg' => 60,
        'additional_charges' => 0,
        'discount_amount' => 0,
        'total_amount' => 300,
        'customer_agreement_version' => '1.0',
        'customer_agreement_accepted' => true,
    ], $extra);
}

it('keeps a single-service order exactly as before', function () {
    $ctx = linesShop();

    $order = app(ShopOrderService::class)->createOrder(linesOrderData($ctx), $ctx['owner']);

    expect($order->serviceLines)->toHaveCount(0)
        ->and((float) $order->total_amount)->toBe(300.0);
});

it('bills a per-kg service and a per-piece service on one order', function () {
    $ctx = linesShop();

    $order = app(ShopOrderService::class)->createOrder(linesOrderData($ctx, [
        // The browser's own total is ignored once extra services are present.
        'total_amount' => 1,
        'service_lines' => [['service_id' => $ctx['comforter']->id, 'piece_count' => 2]],
    ]), $ctx['owner']);

    expect($order->serviceLines)->toHaveCount(1)
        ->and($order->serviceLines->first()->service_name)->toBe('Comforter')
        ->and((float) $order->serviceLines->first()->amount)->toBe(500.0)
        ->and((float) $order->total_amount)->toBe(800.0);

    $breakdown = app(OrderBillingBreakdownService::class)->forOrder($order->fresh());

    expect($breakdown['extra_services'])->toHaveCount(1)
        ->and($breakdown['extra_services'][0]['amount'])->toBe(500.0)
        ->and($breakdown['total'])->toBe(800.0);
});

it('rejects an extra service that belongs to another shop', function () {
    $ctx = linesShop();
    $other = linesShop();

    expect(fn () => app(ShopOrderService::class)->createOrder(linesOrderData($ctx, [
        'service_lines' => [['service_id' => $other['comforter']->id, 'piece_count' => 1]],
    ]), $ctx['owner']))->toThrow(ValidationException::class);

    expect(ShopOrder::count())->toBe(0);
});

it('requires a piece count or weight on each extra service', function () {
    $ctx = linesShop();

    expect(fn () => app(ShopOrderService::class)->createOrder(linesOrderData($ctx, [
        'service_lines' => [['service_id' => $ctx['comforter']->id]],
    ]), $ctx['owner']))->toThrow(ValidationException::class);

    expect(fn () => app(ShopOrderService::class)->createOrder(linesOrderData($ctx, [
        'service_lines' => [['service_id' => $ctx['wash']->id]],
    ]), $ctx['owner']))->toThrow(ValidationException::class);
});

it('creates an order with extra services through the shop endpoint', function () {
    $ctx = linesShop();

    $this->actingAs($ctx['owner'])
        ->post('/shop/operations/orders', linesOrderData($ctx, [
            'service_lines' => [['service_id' => $ctx['comforter']->id, 'piece_count' => 1]],
        ]))
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $order = ShopOrder::first();

    expect($order->serviceLines)->toHaveCount(1)
        ->and((float) $order->total_amount)->toBe(550.0);
});

it('validates extra service lines on the endpoint', function () {
    $ctx = linesShop();

    $this->actingAs($ctx['owner'])
        ->post('/shop/operations/orders', linesOrderData($ctx, [
            'service_lines' => [['piece_count' => 1]],
        ]))
        ->assertSessionHasErrors('service_lines.0.service_id');
});

it('adds, changes and removes extra services when an order is edited', function () {
    $ctx = linesShop();
    $service = app(ShopOrderService::class);
    $order = $service->createOrder(linesOrderData($ctx), $ctx['owner']);

    $editData = fn (array $lines) => [
        'customer_name' => 'Maria Cruz',
        'service_id' => $ctx['wash']->id,
        'pickup_type' => 'walk_in',
        'pricing_model' => 'per_kg',
        'actual_weight_kg' => 5,
        'additional_charges' => 0,
        'discount_amount' => 0,
        'total_amount' => 0,
        'payment_method' => 'cash',
        'status' => 'pending',
        'service_lines' => $lines,
    ];

    $updated = $service->updateOrder($order, $editData([['service_id' => $ctx['comforter']->id, 'piece_count' => 3]]));
    expect($updated->serviceLines)->toHaveCount(1)
        ->and((float) $updated->total_amount)->toBe(1050.0);

    $updated = $service->updateOrder($order->fresh(), $editData([['service_id' => $ctx['comforter']->id, 'piece_count' => 1]]));
    expect((float) $updated->total_amount)->toBe(550.0);

    $updated = $service->updateOrder($order->fresh(), $editData([]));
    expect($updated->serviceLines)->toHaveCount(0)
        ->and((float) $updated->total_amount)->toBe(300.0);
});

// ── Completing an order by hand ──────────────────────────────────────────────

function inProgressLinesOrder(array $ctx, array $overrides = []): ShopOrder
{
    $order = app(ShopOrderService::class)->createOrder(linesOrderData($ctx), $ctx['owner']);
    $order->update(array_merge(['status' => 'in_progress', 'in_progress_at' => now()], $overrides));

    return $order->fresh();
}

it('lets staff complete a weighed in-progress order before its estimated time', function () {
    $ctx = linesShop();
    $order = inProgressLinesOrder($ctx, ['estimated_completion_at' => now()->addHours(5)]);

    $this->actingAs($ctx['owner'])
        ->patch("/shop/operations/orders/{$order->payment_public_id}/status", ['status' => 'completed'])
        ->assertSessionHasNoErrors();

    $order->refresh();
    expect($order->status)->toBe('completed')
        ->and($order->completed_at)->not->toBeNull();
});

it('refuses to complete an order whose weight is not confirmed yet', function () {
    $ctx = linesShop();
    $order = inProgressLinesOrder($ctx, ['actual_weight_kg' => null]);

    $this->actingAs($ctx['owner'])
        ->patch("/shop/operations/orders/{$order->payment_public_id}/status", ['status' => 'completed'])
        ->assertSessionHasErrors('status');

    expect($order->fresh()->status)->toBe('in_progress');
});

it('refuses to complete an order that is still pending', function () {
    $ctx = linesShop();
    $order = inProgressLinesOrder($ctx, ['status' => 'pending']);

    $this->actingAs($ctx['owner'])
        ->patch("/shop/operations/orders/{$order->payment_public_id}/status", ['status' => 'completed'])
        ->assertSessionHasErrors('status');

    expect($order->fresh()->status)->toBe('pending');
});

it('keeps an order open while an additional service still has no weight', function () {
    $ctx = linesShop();
    $order = inProgressLinesOrder($ctx);
    $order->serviceLines()->create([
        'service_id' => $ctx['wash']->id,
        'service_name' => 'Wash and Fold',
        'pricing_model' => 'per_kg',
        'price_per_kg' => 60,
        'amount' => 0,
    ]);

    $this->actingAs($ctx['owner'])
        ->patch("/shop/operations/orders/{$order->payment_public_id}/status", ['status' => 'completed'])
        ->assertSessionHasErrors('status');
});

it('does not let another shop complete the order', function () {
    $ctx = linesShop();
    $other = linesShop();
    $order = inProgressLinesOrder($ctx);

    $this->actingAs($other['owner'])
        ->patch("/shop/operations/orders/{$order->payment_public_id}/status", ['status' => 'completed'])
        ->assertNotFound();

    expect($order->fresh()->status)->toBe('in_progress');
});

// ── Reverting a completion made by mistake ───────────────────────────────────

function linesDelivery(array $ctx, ShopOrder $order, string $status): App\Models\Delivery
{
    return App\Models\Delivery::create([
        'shop_id' => $ctx['shop']->id,
        'shop_order_id' => $order->id,
        'type' => App\Models\Delivery::TYPE_DELIVERY,
        'status' => $status,
        'customer_name' => 'Maria Cruz',
        'address' => '12 Rizal St',
    ]);
}

it('reverts a mistaken completion back to in progress', function () {
    $ctx = linesShop();
    $order = inProgressLinesOrder($ctx);
    $url = "/shop/operations/orders/{$order->payment_public_id}/status";

    $this->actingAs($ctx['owner'])->patch($url, ['status' => 'completed'])->assertSessionHasNoErrors();
    expect($order->fresh()->completed_at)->not->toBeNull();

    $this->actingAs($ctx['owner'])->patch($url, ['status' => 'in_progress'])->assertSessionHasNoErrors();

    $order->refresh();
    expect($order->status)->toBe('in_progress')
        ->and($order->completed_at)->toBeNull();
});

it('withdraws the unstarted delivery when a completion is reverted', function () {
    $ctx = linesShop();
    $order = inProgressLinesOrder($ctx);
    $delivery = linesDelivery($ctx, $order, 'pending');
    $order->update(['status' => 'completed', 'completed_at' => now()]);

    $this->actingAs($ctx['owner'])
        ->patch("/shop/operations/orders/{$order->payment_public_id}/status", ['status' => 'in_progress'])
        ->assertSessionHasNoErrors();

    expect($order->fresh()->status)->toBe('in_progress')
        ->and(App\Models\Delivery::find($delivery->id))->toBeNull();
});

it('blocks the revert once the rider has the laundry', function () {
    $ctx = linesShop();
    $order = inProgressLinesOrder($ctx);
    linesDelivery($ctx, $order, 'picked_up');
    $order->update(['status' => 'completed', 'completed_at' => now()]);

    $this->actingAs($ctx['owner'])
        ->patch("/shop/operations/orders/{$order->payment_public_id}/status", ['status' => 'in_progress'])
        ->assertSessionHasErrors('status');

    expect($order->fresh()->status)->toBe('completed');
});
