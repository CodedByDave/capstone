<?php

use App\Enums\AccountType;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Inertia\Testing\AssertableInertia as Assert;

function createAdminOrder(array $attributes = []): Order
{
    $owner = $attributes['owner'] ?? User::factory()->create([
        'role' => AccountType::ShopOwner->value,
    ]);
    unset($attributes['owner']);

    return Order::create(array_merge([
        'user_id' => $owner->id,
        'shop_name' => 'Order Test Laundry',
        'owner_name' => $owner->name,
        'email' => $owner->email,
        'phone' => '09171234567',
        'municipality' => 'Manila',
        'barangay' => 'Test Barangay',
        'postal_code' => '1000',
        'plan_name' => 'Standard',
        'billing_months' => 12,
        'total_price' => 12000,
        'status' => 'rejected',
    ], $attributes));
}

test('order table supports server-side page sizing', function () {
    $admin = User::factory()->create(['role' => AccountType::SuperAdmin->value]);
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);

    foreach (range(1, 6) as $number) {
        createAdminOrder([
            'owner' => $owner,
            'shop_name' => "Pagination Laundry {$number}",
        ]);
    }

    $this->actingAs($admin)
        ->get(route('admin.orders.index', ['per_page' => 5]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('filters.per_page', '5')
            ->where('orders.per_page', 5)
            ->where('orders.total', 6)
            ->where('orders.last_page', 2)
            ->has('orders.data', 5)
        );
});

test('order revenue uses collected payments without double counting paid orders', function () {
    $admin = User::factory()->create(['role' => AccountType::SuperAdmin->value]);
    $paidOrder = createAdminOrder([
        'status' => 'paid',
        'total_price' => 100,
    ]);
    createAdminOrder([
        'status' => 'paid',
        'total_price' => 50,
    ]);
    Payment::create([
        'order_id' => $paidOrder->id,
        'payment_method' => 'gcash',
        'amount' => 80,
        'status' => 'paid',
        'paid_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get(route('admin.orders.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('stats.revenue', 130)
        );
});

test('order table sorts by shop owner total and expiry columns', function () {
    $admin = User::factory()->create(['role' => AccountType::SuperAdmin->value]);
    createAdminOrder([
        'shop_name' => 'Alpha Laundry',
        'owner_name' => 'Zoe Santos',
        'total_price' => 100,
        'expires_at' => '2026-12-01 00:00:00',
    ]);
    createAdminOrder([
        'shop_name' => 'Zulu Laundry',
        'owner_name' => 'Ana Cruz',
        'total_price' => 900,
        'expires_at' => '2026-10-01 00:00:00',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.orders.index', [
            'sort_by' => 'shop',
            'sort_direction' => 'desc',
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('filters.sort_by', 'shop')
            ->where('filters.sort_direction', 'desc')
            ->where('orders.data.0.shop_name', 'Zulu Laundry')
        );

    $this->actingAs($admin)
        ->get(route('admin.orders.index', [
            'sort_by' => 'owner',
            'sort_direction' => 'asc',
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('orders.data.0.owner_name', 'Ana Cruz')
        );

    $this->actingAs($admin)
        ->get(route('admin.orders.index', [
            'sort_by' => 'total',
            'sort_direction' => 'desc',
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('orders.data.0.total_price', '900.00')
        );

    $this->actingAs($admin)
        ->get(route('admin.orders.index', [
            'sort_by' => 'expires',
            'sort_direction' => 'asc',
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('orders.data.0.shop_name', 'Zulu Laundry')
        );
});

test('order detail exposes the subscribed plan name', function () {
    $admin = User::factory()->create(['role' => AccountType::SuperAdmin->value]);
    $order = createAdminOrder([
        'plan_name' => 'Premium',
        'status' => 'paid',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.orders.show', $order->public_id))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/orders/Show')
            ->where('order.plan_name', 'Premium')
        );
});

test('super admin can export filtered orders as csv', function () {
    $admin = User::factory()->create(['role' => AccountType::SuperAdmin->value]);
    $order = createAdminOrder(['shop_name' => 'CSV Export Laundry']);

    $response = $this->actingAs($admin)->get(route('admin.orders.export', [
        'search' => 'CSV Export Laundry',
    ]));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('text/csv');
    expect($response->streamedContent())
        ->toContain('transaction_reference')
        ->toContain($order->transaction_reference)
        ->toContain('CSV Export Laundry');
});

test('super admin can import an order csv backup', function () {
    $admin = User::factory()->create(['role' => AccountType::SuperAdmin->value]);
    $owner = User::factory()->create([
        'role' => AccountType::ShopOwner->value,
        'email' => 'csv-owner@example.test',
    ]);
    $csv = implode("\n", [
        'transaction_reference,owner_email,shop_name,owner_name,phone,block_street,municipality,barangay,postal_code,plan_name,billing_months,total_price,payment_method,status,expires_at,is_upgrade,is_trial,ordered_at',
        'TXN-CSV-IMPORT,csv-owner@example.test,Imported Laundry,CSV Owner,09170000000,Main Street,Manila,Barangay 1,1000,Basic,1,999,gcash,rejected,,no,no,2026-09-23 12:00:00',
    ]);

    $this->actingAs($admin)
        ->post(route('admin.orders.import'), [
            'file' => UploadedFile::fake()->createWithContent('orders.csv', $csv),
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('orders', [
        'transaction_reference' => 'TXN-CSV-IMPORT',
        'user_id' => $owner->id,
        'shop_name' => 'Imported Laundry',
        'status' => 'rejected',
    ]);

    $order = Order::where('transaction_reference', 'TXN-CSV-IMPORT')->firstOrFail();
    expect($order->modules)->toHaveCount(2);
});
