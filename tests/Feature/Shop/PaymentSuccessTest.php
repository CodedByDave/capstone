<?php

use App\Enums\AccountType;
use App\Models\Order;
use App\Models\User;
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

test('payment success receipt uses the public order id', function () {
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
    $order = createPlanOrderFor($owner);
    $url = route('payment.success', ['order' => $order->public_id]);

    expect($url)
        ->toContain("/shop/payment/success/{$order->public_id}")
        ->not->toContain('order_id=');

    $this->actingAs($owner)
        ->get($url)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('shop/payment/PaymentSuccess')
            ->where('order.public_id', $order->public_id)
            ->missing('order.id'));
});

test('an owner cannot view another owners payment receipt', function () {
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
    $otherOwner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
    $order = createPlanOrderFor($owner);

    $this->actingAs($otherOwner)
        ->get(route('payment.success', ['order' => $order->public_id]))
        ->assertForbidden();
});
