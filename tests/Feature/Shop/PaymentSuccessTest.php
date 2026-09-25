<?php

use App\Enums\AccountType;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Services\PaymongoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

function createPlanOrderFor(User $owner): Order
{
    return Order::create([
        'user_id' => $owner->id,
        'shop_name' => 'Test Laundry',
        'owner_name' => $owner->name,
        'email' => $owner->email,
        'phone' => '09171234567',
        'municipality' => 'Manila',
        'barangay' => 'Ermita',
        'plan_name' => 'Standard',
        'billing_months' => 1,
        'total_price' => 1000,
        'payment_method' => 'gcash',
        'status' => 'approved',
    ]);
}

test('payment success receipt uses a transaction reference instead of the database id', function () {
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
    $order = createPlanOrderFor($owner);
    $url = route('payment.success', ['order' => $order->public_id]);

    expect($url)
        ->toContain("/shop/payment/success/{$order->public_id}")
        ->not->toContain('order_id=');

    expect($order->transaction_reference)
        ->toStartWith('TXN-')
        ->not->toBe('TXN-'.$order->id);

    $this->actingAs($owner)
        ->get($url)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('shop/payment/PaymentSuccess')
            ->where('order.public_id', $order->public_id)
            ->where('order.transaction_reference', $order->transaction_reference)
            ->missing('order.id'));
});

test('each order receives a unique transaction reference', function () {
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);

    $firstOrder = createPlanOrderFor($owner);
    $secondOrder = createPlanOrderFor($owner);

    expect($firstOrder->transaction_reference)
        ->toStartWith('TXN-')
        ->not->toBe($secondOrder->transaction_reference);
});

test('verified payment activates the order and shop without a second approval', function () {
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
    $order = createPlanOrderFor($owner);

    Payment::create([
        'order_id' => $order->id,
        'payment_method' => 'gcash',
        'amount' => $order->total_price,
        'status' => 'pending',
        'paymongo_session_id' => 'cs_test_paid',
    ]);

    $paymongo = $this->mock(PaymongoService::class);
    $paymongo->shouldReceive('getCheckoutSession')
        ->once()
        ->with('cs_test_paid')
        ->andReturn([
            'data' => [
                'attributes' => [
                    'status' => 'completed',
                    'payment_intent' => ['attributes' => ['status' => 'succeeded']],
                ],
            ],
        ]);
    $paymongo->shouldReceive('extractPaymentId')
        ->once()
        ->andReturn('pay_test_paid');

    $this->actingAs($owner)
        ->get(route('payment.success', ['order' => $order->public_id]))
        ->assertOk();

    $order->refresh();

    expect($order->status)->toBe('paid')
        ->and($order->expires_at)->not->toBeNull();

    $this->assertDatabaseHas('payments', [
        'order_id' => $order->id,
        'status' => 'paid',
        'paymongo_payment_id' => 'pay_test_paid',
    ]);
    $this->assertDatabaseHas('shops', [
        'owner_id' => $owner->id,
        'shop_name' => $order->shop_name,
        'status' => 'active',
    ]);
});

test('an owner cannot view another owners payment receipt', function () {
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
    $otherOwner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
    $order = createPlanOrderFor($owner);

    $this->actingAs($otherOwner)
        ->get(route('payment.success', ['order' => $order->public_id]))
        ->assertForbidden();
});
