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
        // An order keeps a copy of the rates it was placed with.
        'price_per_kg' => $service->price_per_kg,
        'bundle_weight_kg' => $service->bundle_weight_kg,
        'bundle_price' => $service->bundle_price,
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

test('weighing a per-kg order keeps its stored additional charges and discount', function () {
    ['order' => $order] = orderTotalScenario();
    $order->update(['additional_charges' => 20, 'discount_amount' => 10, 'total_amount' => 130]);

    $updated = app(ShopOrderService::class)->updateOrder($order->fresh(), [
        'actual_weight_kg' => 3,
        // The charges and discount are locked once the order is placed.
        'additional_charges' => 999,
        'discount_amount' => 0,
    ]);

    // 3 kg x 60 = 180, + 20 - 10
    expect((float) $updated->total_amount)->toBe(190.0)
        ->and((float) $updated->additional_charges)->toBe(20.0);
});

test('weighing a per-bundle order counts its bundles and keeps its charges and discount', function () {
    ['order' => $order] = orderTotalScenario([
        'pricing_model' => 'per_bundle',
        'price_per_kg' => null,
        'bundle_weight_kg' => 5,
        'bundle_price' => 200,
    ]);
    $order->update(['additional_charges' => 50, 'discount_amount' => 25]);

    $updated = app(ShopOrderService::class)->updateOrder($order->fresh(), ['actual_weight_kg' => 6]);

    // ceil(6 / 5) = 2 bundles x 200 = 400, + 50 - 25
    expect($updated->bundle_quantity)->toBe(2)
        ->and((float) $updated->total_amount)->toBe(425.0);
});

test('an unweighed order without extras keeps the plain estimate', function () {
    ['order' => $order] = orderTotalScenario();

    $updated = app(ShopOrderService::class)->updateOrder($order, []);

    expect((float) $updated->total_amount)->toBe(120.0);
});

test('editing an unweighed order leaves its total and delivery fee alone', function () {
    ['order' => $order] = orderTotalScenario();
    $order->update(['delivery_fee' => 45, 'total_amount' => 165]);

    $updated = app(ShopOrderService::class)->updateOrder($order->fresh(), [
        'additional_charges' => 20,
        // A forged fee from the edit form must be ignored.
        'delivery_fee' => 0,
    ]);

    expect((float) $updated->total_amount)->toBe(165.0)
        ->and((float) $updated->delivery_fee)->toBe(45.0);
});

test('a stored delivery fee stays in a weighed order total and keeps the balance due', function () {
    ['order' => $order] = orderTotalScenario();
    $order->update(['delivery_fee' => 45, 'additional_charges' => 20, 'discount_amount' => 10]);
    recordConfirmedPayment($order, 190);

    $updated = app(ShopOrderService::class)->updateOrder($order->fresh(), ['actual_weight_kg' => 3]);

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
