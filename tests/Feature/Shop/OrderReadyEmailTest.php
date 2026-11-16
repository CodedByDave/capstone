<?php

use App\Enums\AccountType;
use App\Mail\OrderReadyMail;
use App\Models\Shop;
use App\Models\ShopOrder;
use App\Models\ShopService;
use App\Models\User;
use App\Notifications\OrderNotification;
use App\Services\ShopOrderService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

function readyShop(): array
{
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
    $shop = Shop::create([
        'owner_id' => $owner->id,
        'shop_name' => 'Suds Laundry',
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

function readyOrder(array $ctx, array $extra = []): ShopOrder
{
    return app(ShopOrderService::class)->createOrder(array_merge([
        'shop_id' => $ctx['shop']->id,
        'customer_name' => 'Henry Santos',
        'customer_phone' => '0917 555 1234',
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

function completeReadyOrder(ShopOrder $order): void
{
    $order->update(['status' => 'in_progress']);
    app(ShopOrderService::class)->updateStatus($order->fresh(), 'completed');
}

test('completing an order with a customer email emails them and tells the owner', function () {
    Mail::fake();
    Notification::fake();
    $ctx = readyShop();
    $order = readyOrder($ctx, ['customer_email' => 'henry@example.com']);

    completeReadyOrder($order);
    // The email goes out after the response; running terminate callbacks stands in for that.
    app()->terminate();

    Notification::assertSentTo($ctx['owner'], OrderNotification::class, fn ($n) => $n->event === 'laundry_done' && $n->emailing);
    Mail::assertSent(OrderReadyMail::class, fn ($mail) => $mail->hasTo('henry@example.com'));
    expect($order->fresh()->ready_email_sent_at)->not->toBeNull();
});

test('completing an order without an email only tells the shop to contact the customer', function () {
    Mail::fake();
    Notification::fake();
    $ctx = readyShop();
    $order = readyOrder($ctx);

    completeReadyOrder($order);

    Notification::assertSentTo($ctx['owner'], OrderNotification::class, fn ($n) => $n->event === 'laundry_done' && ! $n->emailing);
    Mail::assertNothingSent();
    expect($order->fresh()->ready_email_sent_at)->toBeNull();
});
