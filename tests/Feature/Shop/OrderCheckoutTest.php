<?php

use App\Enums\AccountType;
use App\Http\Middleware\EnforceOwnerPlatformPermissions;
use App\Http\Middleware\EnsureCurrentBusinessAgreementAccepted;
use App\Models\Shop;
use App\Models\ShopOrder;
use App\Models\ShopOrderPayment;
use App\Models\ShopService;
use App\Models\User;
use App\Services\ShopOrderService;

beforeEach(function () {
    $this->withoutMiddleware([
        EnsureCurrentBusinessAgreementAccepted::class,
        EnforceOwnerPlatformPermissions::class,
    ]);
});

function checkoutShop(string $name = 'Checkout Laundry'): array
{
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
    $shop = Shop::create([
        'owner_id' => $owner->id,
        'shop_name' => $name.' '.$owner->id,
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Poblacion',
        'postal_code' => '4103',
        'status' => 'active',
        'location_mode' => 'single',
        'setup_completed_at' => now(),
    ]);
    $service = ShopService::create([
        'shop_id' => $shop->id,
        'service_name' => 'Wash and Fold',
        'is_active' => true,
        'pricing_model' => 'per_kg',
        'price_per_kg' => 60,
    ]);

    return compact('owner', 'shop', 'service');
}

/** A ₱300 walk-in order, saved unpaid as it is after "Proceed to Payment". */
function checkoutOrder(array $ctx, array $extra = []): ShopOrder
{
    return app(ShopOrderService::class)->createOrder(array_merge([
        'shop_id' => $ctx['shop']->id,
        'customer_name' => 'Maria Cruz',
        'service_id' => $ctx['service']->id,
        'pickup_type' => 'walk_in',
        'pricing_model' => 'per_kg',
        'actual_weight_kg' => 5,
        'price_per_kg' => 60,
        'additional_charges' => 0,
        'discount_amount' => 0,
        'total_amount' => 300,
        'customer_agreement_version' => '1.0',
        'customer_agreement_accepted' => true,
    ], $extra), $ctx['owner']);
}

function checkoutUrl(ShopOrder $order): string
{
    return "/shop/operations/orders/{$order->payment_public_id}/checkout";
}

test('the payment step shows the order summary and balance', function () {
    $ctx = checkoutShop();
    $order = checkoutOrder($ctx);

    $this->actingAs($ctx['owner'])->get(checkoutUrl($order))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('shop/operations/orders/Checkout')
            ->where('shopOrder.order_number', $order->order_number)
            ->where('summary.total', 300)
            ->where('summary.balance_due', 300)
            ->where('base', '/shop/operations/orders'));
});

test('paying in full with cash records the payment and the change', function () {
    $ctx = checkoutShop();
    $order = checkoutOrder($ctx);

    $this->actingAs($ctx['owner'])
        ->post(checkoutUrl($order), ['payment_mode' => 'full', 'payment_method' => 'cash', 'amount_tendered' => '500'])
        ->assertSessionHasNoErrors()
        ->assertRedirect("/shop/operations/orders/{$order->payment_public_id}")
        ->assertSessionHas('show_receipt', true);

    $payment = ShopOrderPayment::where('shop_order_id', $order->id)->sole();
    expect((float) $payment->amount)->toBe(300.0)
        ->and((float) $payment->change_given)->toBe(200.0)
        ->and($order->fresh()->payment_status)->toBe('paid');
});

test('a partial payment leaves a balance', function () {
    $ctx = checkoutShop();
    $order = checkoutOrder($ctx);

    $this->actingAs($ctx['owner'])
        ->post(checkoutUrl($order), ['payment_mode' => 'partial', 'payment_method' => 'gcash', 'amount' => '100'])
        ->assertSessionHasNoErrors();

    $order->refresh();
    expect($order->payment_status)->toBe('partial')
        ->and($order->balanceDue())->toBe(200.0);
});

test('not paid yet records nothing', function () {
    $ctx = checkoutShop();
    $order = checkoutOrder($ctx);

    $this->actingAs($ctx['owner'])
        ->post(checkoutUrl($order), ['payment_mode' => 'unpaid'])
        ->assertSessionHasNoErrors()
        ->assertRedirect("/shop/operations/orders/{$order->payment_public_id}");

    expect(ShopOrderPayment::where('shop_order_id', $order->id)->exists())->toBeFalse()
        ->and($order->fresh()->payment_status)->toBe('unpaid');
});

test('the payment is checked', function (array $input, string $field) {
    $ctx = checkoutShop();
    $order = checkoutOrder($ctx);

    $this->actingAs($ctx['owner'])
        ->post(checkoutUrl($order), $input)
        ->assertSessionHasErrors($field);

    expect(ShopOrderPayment::where('shop_order_id', $order->id)->exists())->toBeFalse();
})->with([
    'no mode' => [[], 'payment_mode'],
    'no amount for a partial payment' => [['payment_mode' => 'partial', 'payment_method' => 'cash'], 'amount'],
    'more than the balance' => [['payment_mode' => 'partial', 'payment_method' => 'cash', 'amount' => '400'], 'amount'],
    'cash short of the payment' => [['payment_mode' => 'full', 'payment_method' => 'cash', 'amount_tendered' => '200'], 'amount_tendered'],
    'unknown payment type' => [['payment_mode' => 'full', 'payment_method' => 'bitcoin'], 'payment_method'],
]);

test('another shop cannot open or pay the order', function () {
    $mine = checkoutShop('Mine');
    $other = checkoutShop('Other');
    $order = checkoutOrder($mine);

    $this->actingAs($other['owner'])->get(checkoutUrl($order))->assertNotFound();
    $this->actingAs($other['owner'])
        ->post(checkoutUrl($order), ['payment_mode' => 'full', 'payment_method' => 'cash'])
        ->assertNotFound();

    expect(ShopOrderPayment::where('shop_order_id', $order->id)->exists())->toBeFalse();
});

test('creating an order goes to the payment step', function () {
    $ctx = checkoutShop();

    $response = $this->actingAs($ctx['owner'])->post('/shop/operations/orders', [
        'shop_id' => $ctx['shop']->id,
        'customer_name' => 'Maria Cruz',
        'service_id' => $ctx['service']->id,
        'pickup_type' => 'walk_in',
        'pricing_model' => 'per_kg',
        'actual_weight_kg' => 5,
        'price_per_kg' => 60,
        'additional_charges' => 0,
        'discount_amount' => 0,
        'total_amount' => 300,
        'customer_agreement_version' => '1.0',
        'customer_agreement_accepted' => true,
    ])->assertSessionHasNoErrors();

    $order = ShopOrder::where('shop_id', $ctx['shop']->id)->sole();
    $response->assertRedirect(checkoutUrl($order));
    expect($order->payment_status)->toBe('unpaid');
});
