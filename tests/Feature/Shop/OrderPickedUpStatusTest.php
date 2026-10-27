<?php

use App\Enums\AccountType;
use App\Events\OrderPaymentRecorded;
use App\Listeners\StartOrderWhenReady;
use App\Models\Shop;
use App\Models\ShopOrder;
use App\Models\ShopService;
use App\Models\User;
use App\Services\ShopOrderService;
use Illuminate\Validation\ValidationException;

function statusShop(): Shop
{
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);

    return Shop::create([
        'owner_id' => $owner->id,
        'shop_name' => 'Status Laundry '.$owner->id,
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Poblacion',
        'postal_code' => '4103',
        'status' => 'active',
        'setup_completed_at' => now(),
    ]);
}

function statusOrder(Shop $shop, array $overrides = []): ShopOrder
{
    $service = ShopService::firstOrCreate(
        ['shop_id' => $shop->id, 'service_name' => 'Wash & Fold'],
        ['is_active' => true, 'pricing_model' => 'per_kg', 'price_per_kg' => 60],
    );

    return ShopOrder::create(array_merge([
        'shop_id' => $shop->id,
        'order_source' => 'shop',
        'service_id' => $service->id,
        'order_number' => 'ORD-'.strtoupper(uniqid()),
        'customer_name' => 'Maria S.',
        'pickup_type' => 'walk_in',
        'pricing_model' => 'per_kg',
        'price_per_kg' => 60,
        'estimated_weight_kg' => 5,
        'total_amount' => 300,
        'payment_status' => 'unpaid',
        'amount_paid' => 0,
        'status' => 'pending',
    ], $overrides));
}

it('starts a pending order once it is fully paid, or its price is final', function () {
    $shop = statusShop();
    $orders = app(ShopOrderService::class);

    // Unpaid and not weighed yet: nothing to start from.
    $partial = statusOrder($shop, ['payment_status' => 'partial', 'amount_paid' => 100]);
    $paid = statusOrder($shop, ['payment_status' => 'paid', 'amount_paid' => 300]);
    $done = statusOrder($shop, ['payment_status' => 'paid', 'amount_paid' => 300, 'status' => 'completed']);

    foreach ([$partial, $paid, $done] as $order) {
        $orders->startWhenReady($order);
    }

    expect($partial->fresh()->status)->toBe('pending')
        ->and($paid->fresh()->status)->toBe('in_progress')
        ->and($paid->fresh()->in_progress_at)->not->toBeNull()
        ->and($done->fresh()->status)->toBe('completed');
});

it('reacts to a recorded payment through the listener', function () {
    $shop = statusShop();
    $order = statusOrder($shop, ['payment_status' => 'paid', 'amount_paid' => 300]);

    app(StartOrderWhenReady::class)->handle(new OrderPaymentRecorded($order));

    expect($order->fresh()->status)->toBe('in_progress');
});

it('marks a completed, fully paid order as picked up', function () {
    $shop = statusShop();
    $order = statusOrder($shop, ['status' => 'completed', 'payment_status' => 'paid', 'amount_paid' => 300]);

    app(ShopOrderService::class)->updateStatus($order, 'picked_up');

    expect($order->fresh()->status)->toBe('picked_up')
        ->and($order->fresh()->picked_up_at)->not->toBeNull()
        ->and($order->fresh()->isCompleted())->toBeTrue();
});

it('does not allow picked up before completed, with a balance, or for a delivered order', function () {
    $shop = statusShop();
    $orders = app(ShopOrderService::class);

    $notDone = statusOrder($shop, ['status' => 'in_progress', 'payment_status' => 'paid', 'amount_paid' => 300]);
    $owing = statusOrder($shop, ['status' => 'completed', 'payment_status' => 'partial', 'amount_paid' => 100]);
    $delivery = statusOrder($shop, ['status' => 'completed', 'payment_status' => 'paid', 'amount_paid' => 300, 'deliver_to_customer' => true]);

    foreach ([$notDone, $owing, $delivery] as $order) {
        expect(fn () => $orders->updateStatus($order, 'picked_up'))->toThrow(ValidationException::class);
        expect($order->fresh()->status)->not->toBe('picked_up');
    }

    try {
        $orders->updateStatus($owing, 'picked_up');
    } catch (ValidationException $e) {
        expect($e->errors()['status'][0])->toContain('₱200.00');
    }
});

it('counts a picked up order as finished for the dashboard and ratings', function () {
    $shop = statusShop();
    statusOrder($shop, ['status' => 'picked_up']);
    statusOrder($shop, ['status' => 'completed']);
    statusOrder($shop, ['status' => 'pending']);

    expect(ShopOrder::forShop($shop->id)->completed()->count())->toBe(2)
        ->and(ShopOrder::FINISHED_STATUSES)->toBe(['completed', 'picked_up']);
});

it('starts an unpaid order once its price is final, so paying at the end does not hold it up', function () {
    $shop = statusShop();
    $orders = app(ShopOrderService::class);

    $weighed = statusOrder($shop, ['actual_weight_kg' => 3]);
    $notWeighed = statusOrder($shop, ['actual_weight_kg' => null]);
    $perPiece = statusOrder($shop, ['pricing_model' => 'per_piece', 'piece_count' => 2, 'price_per_piece' => 250]);
    $done = statusOrder($shop, ['actual_weight_kg' => 3, 'status' => 'completed']);

    foreach ([$weighed, $notWeighed, $perPiece, $done] as $order) {
        $orders->startWhenReady($order);
    }

    expect($weighed->fresh()->status)->toBe('in_progress')
        ->and($weighed->fresh()->payment_status)->toBe('unpaid')
        ->and($notWeighed->fresh()->status)->toBe('pending')
        ->and($perPiece->fresh()->status)->toBe('in_progress')
        ->and($done->fresh()->status)->toBe('completed');
});

it('starts a weighed walk-in order the moment staff create it, paid or not', function () {
    $shop = statusShop();
    $service = App\Models\ShopService::create([
        'shop_id' => $shop->id, 'service_name' => 'Wash & Fold', 'is_active' => true,
        'pricing_model' => 'per_kg', 'price_per_kg' => 60,
    ]);
    $base = [
        'shop_id' => $shop->id, 'customer_name' => 'Maria S.', 'service_id' => $service->id,
        'pickup_type' => 'walk_in', 'pricing_model' => 'per_kg', 'price_per_kg' => 60,
        'total_amount' => 180, 'amount_paid' => 0,
        'customer_agreement_version' => app(App\Services\CustomerAgreementService::class)->agreementDataForShopId($shop->id)['version'],
        'customer_agreement_accepted' => true,
    ];

    $weighed = app(ShopOrderService::class)->createOrder([...$base, 'actual_weight_kg' => 3], $shop->owner);
    $unweighed = app(ShopOrderService::class)->createOrder([...$base, 'actual_weight_kg' => null], $shop->owner);

    expect($weighed->status)->toBe('in_progress')
        ->and($weighed->payment_status)->toBe('unpaid')
        ->and($unweighed->status)->toBe('pending');
});
