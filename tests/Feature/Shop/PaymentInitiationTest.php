<?php

use App\Enums\AccountType;
use App\Models\Order;
use App\Models\User;
use App\Services\PaymongoService;

function approvedPayableOrder(User $owner): Order
{
    return Order::create([
        'user_id' => $owner->id,
        'shop_name' => 'Payment Laundry',
        'owner_name' => $owner->name,
        'email' => $owner->email,
        'phone' => '09171234567',
        'municipality' => 'City of Imus',
        'barangay' => 'Anabu I-A',
        'postal_code' => '4103',
        'plan_name' => 'Standard',
        'billing_months' => 1,
        'total_price' => 7056,
        'status' => 'approved',
        'is_trial' => false,
    ]);
}

test('an owner can initiate payment for the latest approved order', function () {
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
    $order = approvedPayableOrder($owner);

    $paymongo = $this->mock(PaymongoService::class);
    $paymongo->shouldReceive('createCheckoutSession')
        ->once()
        ->withArgs(fn (Order $payableOrder) => $payableOrder->is($order)
            && $payableOrder->payment_method === 'gcash')
        ->andReturn([
            'data' => [
                'id' => 'cs_test_order_payment',
                'attributes' => [
                    'checkout_url' => 'https://checkout.paymongo.test/session',
                ],
            ],
        ]);

    $this->actingAs($owner)
        ->post(route('shop.payment.pay'), ['payment_method' => 'gcash'])
        ->assertRedirect('https://checkout.paymongo.test/session')
        ->assertSessionHas('pending_order_id', $order->id);

    $this->assertDatabaseHas('orders', [
        'id' => $order->id,
        'payment_method' => 'gcash',
    ]);
    $this->assertDatabaseHas('payments', [
        'order_id' => $order->id,
        'payment_method' => 'gcash',
        'status' => 'pending',
        'paymongo_session_id' => 'cs_test_order_payment',
    ]);
});

test('payment initiation rejects unsupported payment methods', function () {
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
    approvedPayableOrder($owner);

    $this->mock(PaymongoService::class)
        ->shouldNotReceive('createCheckoutSession');

    $this->actingAs($owner)
        ->post(route('shop.payment.pay'), ['payment_method' => 'cash'])
        ->assertSessionHasErrors('payment_method');

    $this->assertDatabaseCount('payments', 0);
});
