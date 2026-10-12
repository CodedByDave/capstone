<?php

use App\Enums\AccountType;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

function paymentHistoryOrder(User $owner, array $overrides = []): Order
{
    return Order::create(array_merge([
        'user_id' => $owner->id,
        'shop_name' => 'History Laundry',
        'owner_name' => $owner->name,
        'email' => $owner->email,
        'phone' => '09171234567',
        'municipality' => 'Manila',
        'barangay' => 'Ermita',
        'postal_code' => '1000',
        'plan_name' => 'Standard',
        'billing_months' => 1,
        'total_price' => 1500,
        'payment_method' => 'gcash',
        'status' => 'paid',
        'expires_at' => now()->addMonth(),
    ], $overrides));
}

test('a shop owner can view only successful payments in payment history', function () {
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
    $otherOwner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
    $paidOrder = paymentHistoryOrder($owner);
    $pendingOrder = paymentHistoryOrder($owner, ['shop_name' => 'Pending Laundry']);
    $otherOrder = paymentHistoryOrder($otherOwner, ['shop_name' => 'Other Laundry']);

    Payment::create([
        'order_id' => $paidOrder->id,
        'payment_method' => 'gcash',
        'amount' => 1500,
        'status' => 'paid',
        'paid_at' => now(),
    ]);
    Payment::create([
        'order_id' => $pendingOrder->id,
        'payment_method' => 'card',
        'amount' => 1500,
        'status' => 'pending',
    ]);
    Payment::create([
        'order_id' => $otherOrder->id,
        'payment_method' => 'maya',
        'amount' => 2500,
        'status' => 'paid',
        'paid_at' => now(),
    ]);

    $this->actingAs($owner)
        ->get(route('shop.payment-history.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('shop/payment/History')
            ->where('payments.total', 1)
            ->where('payments.data.0.order_public_id', $paidOrder->public_id)
            ->where('payments.data.0.transaction_reference', $paidOrder->transaction_reference)
            ->where('summary.payment_count', 1)
            ->where('summary.total_paid', 1500));
});

test('payment history supports server-side filtering and sorting', function () {
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
    $gcashOrder = paymentHistoryOrder($owner, ['plan_name' => 'Basic', 'total_price' => 1000]);
    $cardOrder = paymentHistoryOrder($owner, ['plan_name' => 'Premium', 'total_price' => 3000, 'payment_method' => 'card']);

    Payment::create([
        'order_id' => $gcashOrder->id,
        'payment_method' => 'gcash',
        'amount' => 1000,
        'status' => 'paid',
        'paid_at' => now()->subDay(),
    ]);
    Payment::create([
        'order_id' => $cardOrder->id,
        'payment_method' => 'card',
        'amount' => 3000,
        'status' => 'paid',
        'paid_at' => now(),
    ]);

    $this->actingAs($owner)
        ->get(route('shop.payment-history.index', [
            'payment_method' => 'card',
            'sort_by' => 'amount',
            'sort_direction' => 'desc',
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('payments.total', 1)
            ->where('payments.data.0.plan_name', 'Premium')
            ->where('filters.payment_method', 'card'));
});

test('a shop owner can reopen and download data for an official paid receipt', function () {
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
    $order = paymentHistoryOrder($owner);
    $paidAt = now()->subHour()->startOfSecond();

    Payment::create([
        'order_id' => $order->id,
        'payment_method' => 'gcash',
        'amount' => 1500,
        'status' => 'paid',
        'paymongo_payment_id' => 'pay_official_receipt',
        'paid_at' => $paidAt,
    ]);

    $this->actingAs($owner)
        ->get(route('shop.payment-history.show', $order->public_id))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('shop/payment/PaymentSuccess')
            ->where('history', true)
            ->where('order.transaction_reference', $order->transaction_reference)
            ->where('order.paymongo_payment_id', 'pay_official_receipt')
            ->where('order.paid_at', $paidAt->toISOString()));
});

test('a shop owner cannot access another shops receipt or a receipt for an unpaid attempt', function () {
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
    $otherOwner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
    $paidOrder = paymentHistoryOrder($owner);
    $unpaidOrder = paymentHistoryOrder($otherOwner, ['status' => 'approved']);

    Payment::create([
        'order_id' => $paidOrder->id,
        'payment_method' => 'gcash',
        'amount' => 1500,
        'status' => 'paid',
        'paid_at' => now(),
    ]);
    Payment::create([
        'order_id' => $unpaidOrder->id,
        'payment_method' => 'card',
        'amount' => 1500,
        'status' => 'pending',
    ]);

    $this->actingAs($otherOwner)
        ->get(route('shop.payment-history.show', $paidOrder->public_id))
        ->assertForbidden();

    $this->actingAs($otherOwner)
        ->get(route('shop.payment-history.show', $unpaidOrder->public_id))
        ->assertNotFound();
});
