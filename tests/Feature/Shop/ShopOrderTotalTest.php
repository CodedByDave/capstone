<?php

use App\Enums\AccountType;
use App\Models\Shop;
use App\Models\ShopOrder;
use App\Models\ShopService;
use App\Models\User;
use App\Services\OrderPaymentService;
use App\Services\ShopOrderService;

function orderTotalScenario(array $serviceOverrides = []): array
{
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
    $shop = Shop::create([
        'owner_id' => $owner->id,
        'shop_name' => 'Total Laundry',
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Poblacion',
        'postal_code' => '4103',
        'status' => 'active',
        'location_mode' => 'single',
        'setup_completed_at' => now(),
    ]);
    $service = ShopService::create(array_merge([
        'shop_id' => $shop->id,
        'service_name' => 'Wash and Fold',
        'is_active' => true,
        'pricing_model' => 'per_kg',
        'price_per_kg' => 60,
    ], $serviceOverrides));

    $order = ShopOrder::create([
        'shop_id' => $shop->id,
        'order_source' => 'shop',
        'service_id' => $service->id,
        'order_number' => 'ORD-TOTAL-1',
        'customer_name' => 'Walk In',
        'pickup_type' => 'walk_in',
        'pricing_model' => $service->pricing_model,
        'estimated_weight_kg' => 2,
        'total_amount' => 120,
        'payment_status' => 'unpaid',
        'status' => 'pending',
    ]);

    return compact('order', 'service');
}

/** Money already received for an order, kept in the payment history. */
function recordConfirmedPayment(ShopOrder $order, float $amount): void
{
    $order->payments()->create([
        'shop_id' => $order->shop_id,
        'amount' => $amount,
        'method' => 'cash',
        'status' => 'confirmed',
        'source' => 'shop',
        'paid_at' => now(),
    ]);
    $order->update(['amount_paid' => $amount, 'payment_status' => 'partial']);
}

function orderUpdatePayload(ShopService $service, array $overrides = []): array
{
    return array_merge([
        'service_id' => $service->id,
        'customer_name' => 'Walk In',
        'pickup_type' => 'walk_in',
        'estimated_weight_kg' => 2,
        'actual_weight_kg' => null,
        'additional_charges' => 0,
        'discount_amount' => 0,
        'status' => 'pending',
    ], $overrides);
}

test('an unweighed per-kg order total keeps additional charges and discount', function () {
    ['order' => $order, 'service' => $service] = orderTotalScenario();

    $updated = app(ShopOrderService::class)->updateOrder($order, orderUpdatePayload($service, [
        'additional_charges' => 20,
        'discount_amount' => 10,
    ]));

    // 2 kg x 60 = 120 base, + 20 charges - 10 discount
    expect((float) $updated->total_amount)->toBe(130.0);
});

test('an unweighed per-bundle order total keeps additional charges and discount', function () {
    ['order' => $order, 'service' => $service] = orderTotalScenario([
        'pricing_model' => 'per_bundle',
        'price_per_kg' => null,
        'bundle_weight_kg' => 5,
        'bundle_price' => 200,
    ]);

    $updated = app(ShopOrderService::class)->updateOrder($order, orderUpdatePayload($service, [
        'estimated_weight_kg' => 6,
        'additional_charges' => 50,
        'discount_amount' => 25,
    ]));

    // ceil(6 / 5) = 2 bundles x 200 = 400, + 50 - 25
    expect((float) $updated->total_amount)->toBe(425.0);
});

test('a weighed order total still uses the actual weight with charges and discount', function () {
    ['order' => $order, 'service' => $service] = orderTotalScenario();

    $updated = app(ShopOrderService::class)->updateOrder($order, orderUpdatePayload($service, [
        'actual_weight_kg' => 3,
        'additional_charges' => 20,
        'discount_amount' => 10,
    ]));

    // 3 kg x 60 = 180, + 20 - 10
    expect((float) $updated->total_amount)->toBe(190.0);
});

test('an unweighed order without extras keeps the plain estimate', function () {
    ['order' => $order, 'service' => $service] = orderTotalScenario();

    $updated = app(ShopOrderService::class)->updateOrder($order, orderUpdatePayload($service));

    expect((float) $updated->total_amount)->toBe(120.0);
});

test('a stored delivery fee stays in an unweighed order total with charges and discount', function () {
    ['order' => $order, 'service' => $service] = orderTotalScenario();
    $order->update(['delivery_fee' => 45]);

    $updated = app(ShopOrderService::class)->updateOrder($order->fresh(), orderUpdatePayload($service, [
        'additional_charges' => 20,
        'discount_amount' => 10,
        // A forged fee from the edit form must be ignored.
        'delivery_fee' => 0,
    ]));

    // 2 kg x 60 = 120, + 20 - 10, + 45 delivery
    expect((float) $updated->total_amount)->toBe(175.0)
        ->and((float) $updated->delivery_fee)->toBe(45.0);
});

test('a stored delivery fee stays in a weighed order total and keeps the balance due', function () {
    ['order' => $order, 'service' => $service] = orderTotalScenario();
    $order->update(['delivery_fee' => 45]);
    recordConfirmedPayment($order, 190);

    $updated = app(ShopOrderService::class)->updateOrder($order->fresh(), orderUpdatePayload($service, [
        'actual_weight_kg' => 3,
        'additional_charges' => 20,
        'discount_amount' => 10,
        'status' => 'completed',
    ]));

    // 3 kg x 60 = 180, + 20 - 10 = 190 paid, + 45 delivery still owed
    expect((float) $updated->total_amount)->toBe(235.0)
        ->and($updated->payment_status)->toBe('partial')
        ->and($updated->balanceDue())->toBe(45.0);
});

test('recording the delivery fee payment marks the order paid', function () {
    ['order' => $order] = orderTotalScenario();
    $order->update(['delivery_fee' => 45, 'total_amount' => 165]);
    recordConfirmedPayment($order, 120);

    $payment = app(OrderPaymentService::class)->recordShopPayment(
        $order->shop->owner,
        $order->fresh(),
        ['amount' => 45, 'method' => 'cash'],
    );

    expect((float) $payment->amount)->toBe(45.0)
        ->and($order->fresh()->payment_status)->toBe('paid');
});
