<?php

use App\Enums\AccountType;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('analytics overview returns complete trends and collected revenue', function () {
    $admin = User::factory()->create(['role' => AccountType::SuperAdmin->value]);
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);

    $paidOrder = Order::create([
        'user_id' => $owner->id,
        'shop_name' => 'Analytics Laundry',
        'owner_name' => $owner->name,
        'email' => $owner->email,
        'phone' => '09171234567',
        'municipality' => 'Manila',
        'barangay' => 'Test Barangay',
        'postal_code' => '1000',
        'plan_name' => 'Standard',
        'billing_months' => 12,
        'total_price' => 1000,
        'status' => 'paid',
    ]);
    Payment::create([
        'order_id' => $paidOrder->id,
        'payment_method' => 'gcash',
        'amount' => 800,
        'status' => 'paid',
        'paid_at' => now(),
    ]);
    Order::create([
        'user_id' => $owner->id,
        'shop_name' => 'Legacy Analytics Laundry',
        'owner_name' => $owner->name,
        'email' => $owner->email,
        'phone' => '09171234567',
        'municipality' => 'Manila',
        'barangay' => 'Test Barangay',
        'postal_code' => '1000',
        'plan_name' => 'Basic',
        'billing_months' => 1,
        'total_price' => 500,
        'status' => 'paid',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.analytics'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/analytics/Index')
            ->where('stats.total_revenue.value', 1300)
            ->has('revenue_chart', 12)
            ->has('orders_chart', 12)
            ->has('registrations_chart', 12)
        );
});
