<?php

use App\Enums\AccountType;
use App\Models\ActivityLog;
use App\Models\Order;
use App\Models\Shop;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

function createManagedShop(array $attributes = []): Shop
{
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);

    return Shop::create(array_merge([
        'owner_id' => $owner->id,
        'shop_name' => 'Clean Test Laundry',
        'phone' => '09171234567',
        'municipality' => 'Manila',
        'barangay' => 'Test Barangay',
        'postal_code' => '1000',
        'postal_code' => '1000',
        'status' => 'active',
        'bir_expiry_date' => now()->addYear(),
        'dti_expiry_date' => now()->addYear(),
        'mayors_expiry_date' => now()->addYear(),
        'sanitary_expiry_date' => now()->addYear(),
        'last_activity_at' => now(),
    ], $attributes));
}

test('super admin can view shop health and compliance data', function () {
    $admin = User::factory()->create(['role' => AccountType::SuperAdmin->value]);
    createManagedShop(['shop_name' => 'Healthy Laundry']);
    createManagedShop([
        'shop_name' => 'Permit Alert Laundry',
        'bir_expiry_date' => now()->subDay(),
        'last_activity_at' => now()->subDays(40),
    ]);

    $this->actingAs($admin)
        ->get(route('shop.index', ['compliance' => 'expired']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/shop/Index')
            ->where('stats.total', 2)
            ->where('stats.expired_permits', 1)
            ->where('stats.compliance_issues', 1)
            ->where('stats.inactive', 1)
            ->where('stats.needs_attention', 1)
            ->has('shops.data', 1)
            ->where('shops.data.0.health_status', 'critical')
        );
});

test('shop table supports sorting across management columns and page sizing', function () {
    $admin = User::factory()->create(['role' => AccountType::SuperAdmin->value]);
    $zoe = User::factory()->create([
        'role' => AccountType::ShopOwner->value,
        'name' => 'Zoe Santos',
    ]);
    $ana = User::factory()->create([
        'role' => AccountType::ShopOwner->value,
        'name' => 'Ana Cruz',
    ]);
    createManagedShop([
        'owner_id' => $zoe->id,
        'shop_name' => 'Alpha Laundry',
        'status' => 'active',
        'last_activity_at' => now(),
    ]);
    createManagedShop([
        'owner_id' => $ana->id,
        'shop_name' => 'Zulu Laundry',
        'status' => 'disabled',
        'bir_expiry_date' => now()->subDay(),
        'last_activity_at' => now()->subDays(40),
    ]);
    Order::create([
        'user_id' => $zoe->id,
        'shop_name' => 'Alpha Laundry',
        'owner_name' => $zoe->name,
        'email' => $zoe->email,
        'phone' => '09171234567',
        'municipality' => 'Manila',
        'barangay' => 'Test Barangay',
        'postal_code' => '1000',
        'plan_name' => 'Standard',
        'billing_months' => 12,
        'total_price' => 12000,
        'status' => 'paid',
        'expires_at' => now()->addYear(),
    ]);
    Order::create([
        'user_id' => $ana->id,
        'shop_name' => 'Zulu Laundry',
        'owner_name' => $ana->name,
        'email' => $ana->email,
        'phone' => '09171234567',
        'municipality' => 'Manila',
        'barangay' => 'Test Barangay',
        'postal_code' => '1000',
        'plan_name' => 'Premium',
        'billing_months' => 12,
        'total_price' => 18000,
        'status' => 'paid',
        'expires_at' => now()->addYear(),
    ]);

    $this->actingAs($admin)
        ->get(route('shop.index', [
            'sort_by' => 'shop_name',
            'sort_direction' => 'asc',
            'per_page' => 5,
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('shops.per_page', 5)
            ->where('shops.data.0.shop_name', 'Alpha Laundry')
            ->where('shops.data.1.shop_name', 'Zulu Laundry')
            ->where('filters.sort_by', 'shop_name')
            ->where('filters.sort_direction', 'asc')
        );

    $expectedFirstShop = [
        'owner' => 'Zulu Laundry',
        'health_status' => 'Zulu Laundry',
        'compliance_status' => 'Alpha Laundry',
        'subscription' => 'Zulu Laundry',
        'last_activity_at' => 'Zulu Laundry',
        'status' => 'Alpha Laundry',
    ];

    foreach ($expectedFirstShop as $sortBy => $shopName) {
        $this->actingAs($admin)
            ->get(route('shop.index', [
                'sort_by' => $sortBy,
                'sort_direction' => 'asc',
            ]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('shops.data.0.shop_name', $shopName)
                ->where('filters.sort_by', $sortBy)
            );
    }
});

test('approval allows payment but activation and permit sync wait until payment', function () {
    $admin = User::factory()->create(['role' => AccountType::SuperAdmin->value]);
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
    $expiry = now()->addYear()->toDateString();
    $order = Order::create([
        'user_id' => $owner->id,
        'shop_name' => 'Permit Complete Laundry',
        'owner_name' => $owner->name,
        'email' => $owner->email,
        'phone' => '09171234567',
        'municipality' => 'Manila',
        'barangay' => 'Test Barangay',
        'postal_code' => '1000',
        'plan_name' => 'Standard',
        'billing_months' => 12,
        'total_price' => 12000,
        'status' => 'pending',
        'kyc_bir' => 'kyc/bir.jpg',
        'kyc_dti' => 'kyc/dti.jpg',
        'kyc_mayors' => 'kyc/mayors.jpg',
        'bir_expiry_date' => $expiry,
        'dti_expiry_date' => $expiry,
        'mayors_expiry_date' => $expiry,
        'sanitary_expiry_date' => null,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.orders.approve', $order->public_id))
        ->assertRedirect();

    expect($order->fresh()->status)->toBe('approved');
    $this->assertDatabaseMissing('shops', ['owner_id' => $owner->id]);

    $this->actingAs($admin)
        ->post(route('admin.orders.approve', $order->public_id))
        ->assertStatus(409);

    $order->update([
        'status' => 'paid',
        'expires_at' => now()->addMonths($order->billing_months),
    ]);
    app(\App\Services\OrderService::class)->syncApprovedShop($order);

    $this->assertDatabaseHas('shops', [
        'owner_id' => $owner->id,
        'bir_expiry_date' => $expiry,
        'dti_expiry_date' => $expiry,
        'mayors_expiry_date' => $expiry,
        'sanitary_expiry_date' => null,
    ]);

    $this->actingAs($admin)
        ->get(route('shop.index', ['search' => 'Permit Complete Laundry']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('shops.data.0.compliance_status', 'compliant')
            ->where('stats.compliance_issues', 0)
        );

    $shop = Shop::where('owner_id', $owner->id)->firstOrFail();
    $this->actingAs($admin)
        ->get(route('shop.show', $shop->public_id))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/shop/Show')
            ->where('shop.owner.public_id', $owner->public_id)
            ->where('shop.bir_expiry_date', $expiry)
            ->where('shop.permit_files.bir', 'kyc/bir.jpg')
            ->where('shop.permit_files.dti', 'kyc/dti.jpg')
            ->where('shop.permit_files.mayors', 'kyc/mayors.jpg')
            ->where('shop.permit_files.sanitary', null)
            ->where('shop.employees_count', 0)
            ->where('shop.services_count', 0)
            ->where('shop.orders_count', 0)
            ->missing('shop.paymongo_secret_key')
        );

    $this->actingAs($admin)
        ->get('/admin/shop/'.$shop->id)
        ->assertNotFound();
});

test('shop archive restore and disable actions are audited', function () {
    $admin = User::factory()->create(['role' => AccountType::SuperAdmin->value]);
    $shop = createManagedShop();

    $this->actingAs($admin)
        ->post(route('shop.disable', $shop->public_id), ['reason' => 'Compliance review'])
        ->assertRedirect();

    $this->actingAs($admin)
        ->delete(route('shop.destroy', $shop->public_id))
        ->assertRedirect();

    $this->assertSoftDeleted($shop);
    $this->assertDatabaseHas('activity_logs', [
        'module' => 'Shop Management',
        'action' => 'disabled',
        'subject_id' => $shop->id,
        'performed_by' => $admin->id,
    ]);
    $this->assertDatabaseHas('activity_logs', [
        'module' => 'Shop Management',
        'action' => 'archived',
        'subject_id' => $shop->id,
        'performed_by' => $admin->id,
    ]);

    $this->actingAs($admin)
        ->post(route('shop.restore', $shop->public_id))
        ->assertRedirect();

    $this->assertNotSoftDeleted($shop);
    expect(ActivityLog::where('module', 'Shop Management')->where('action', 'restored')->exists())->toBeTrue();
});

test('shop bulk actions and csv export work', function () {
    $admin = User::factory()->create(['role' => AccountType::SuperAdmin->value]);
    $first = createManagedShop(['shop_name' => 'First Bulk Shop']);
    $second = createManagedShop(['shop_name' => 'Second Bulk Shop']);

    $this->actingAs($admin)
        ->post(route('shop.bulk-action'), [
            'ids' => [$first->id, $second->id],
            'action' => 'archive',
        ])
        ->assertRedirect();

    $this->assertSoftDeleted($first);
    $this->assertSoftDeleted($second);

    $this->actingAs($admin)
        ->get(route('shop.index', ['trashed' => 1]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->has('shops.data', 2));

    $this->actingAs($admin)
        ->get(route('shop.export', ['trashed' => 1]))
        ->assertOk()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8');
});
