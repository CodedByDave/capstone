<?php

use App\Enums\AccountType;
use App\Http\Middleware\EnforceOwnerPlatformPermissions;
use App\Http\Middleware\EnsureCurrentBusinessAgreementAccepted;
use App\Models\Order;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function setupOwnerWithPlan(string $plan = 'Basic', bool $trial = false): array
{
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
    $shop = Shop::create([
        'owner_id' => $owner->id,
        'shop_name' => 'Wizard Test Laundry',
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Poblacion',
        'postal_code' => '4103',
        'status' => 'pending_setup',
    ]);
    $order = Order::create([
        'user_id' => $owner->id,
        'shop_name' => $shop->shop_name,
        'owner_name' => $owner->name,
        'email' => $owner->email,
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Poblacion',
        'postal_code' => '4103',
        'plan_name' => $plan,
        'billing_months' => 1,
        'is_trial' => $trial,
        'total_price' => $trial ? 0 : 3800,
        'status' => $trial ? 'approved' : 'paid',
        'expires_at' => now()->addMonth(),
    ]);

    return compact('owner', 'shop', 'order');
}

function setupBranchPayload(): array
{
    return [
        'name' => 'Molino Branch',
        'phone' => '09181234567',
        'block_street' => '2 Molino Road',
        'municipality' => 'Bacoor',
        'barangay' => 'Molino I',
        'postal_code' => '4102',
        'mayors_permit_number' => 'BAC-2026-100',
        'mayors_permit' => UploadedFile::fake()->create('mayors.pdf', 100, 'application/pdf'),
        'mayors_expiry_date' => now()->addYear()->toDateString(),
        'sanitary_permit_number' => 'SAN-2026-100',
        'sanitary_permit' => UploadedFile::fake()->create('sanitary.pdf', 100, 'application/pdf'),
        'sanitary_expiry_date' => now()->addYear()->toDateString(),
    ];
}

beforeEach(function () {
    $this->withoutMiddleware([
        EnsureCurrentBusinessAgreementAccepted::class,
        EnforceOwnerPlatformPermissions::class,
    ]);
});

test('active owner with incomplete setup is redirected from dashboard to wizard', function () {
    ['owner' => $owner] = setupOwnerWithPlan();

    $this->actingAs($owner)
        ->get(route('shop.dashboard'))
        ->assertRedirect(route('shop.setup.show'));
});

test('basic owner can complete single location setup', function () {
    ['owner' => $owner, 'shop' => $shop] = setupOwnerWithPlan();

    $this->actingAs($owner)
        ->put(route('shop.setup.store'), [
            'location_mode' => 'single',
            'offers_pickup' => true,
            'offers_delivery' => false,
        ])
        ->assertRedirect(route('shop.dashboard'));

    $shop->refresh();

    expect($shop->location_mode)->toBe('single')
        ->and($shop->offers_pickup)->toBeTrue()
        ->and($shop->offers_delivery)->toBeFalse()
        ->and($shop->setup_completed_at)->not->toBeNull()
        ->and($shop->status)->toBe('active');
});

test('basic and standard plans cannot enable multiple locations', function (string $plan) {
    ['owner' => $owner, 'shop' => $shop] = setupOwnerWithPlan($plan);

    $this->actingAs($owner)
        ->from(route('shop.setup.show'))
        ->put(route('shop.setup.store'), [
            'location_mode' => 'multiple',
            'offers_pickup' => false,
            'offers_delivery' => false,
        ])
        ->assertRedirect(route('shop.setup.show'))
        ->assertSessionHasErrors('location_mode');

    expect($shop->fresh()->setup_completed_at)->toBeNull();
})->with(['Basic', 'Standard']);

test('premium and trial plans can enable multiple locations', function (string $plan, bool $trial) {
    Storage::fake('private');
    ['owner' => $owner, 'shop' => $shop] = setupOwnerWithPlan($plan, $trial);

    $this->actingAs($owner)
        ->post(route('shop.setup.store'), [
            '_method' => 'put',
            'location_mode' => 'multiple',
            'ownership_type' => 'company_owned',
            'branches' => [setupBranchPayload()],
            'offers_pickup' => true,
            'offers_delivery' => true,
        ])
        ->assertRedirect(route('shop.dashboard'));

    expect($shop->fresh()->location_mode)->toBe('multiple')
        ->and($shop->fresh()->ownership_type)->toBe('company_owned');

    $this->assertDatabaseHas('branches', [
        'shop_id' => $shop->id,
        'name' => 'Molino Branch',
        'mayors_permit_number' => 'BAC-2026-100',
    ]);
})->with([
    'premium' => ['Premium', false],
    'trial' => ['Standard', true],
]);

test('franchise setup records the brand separately from account ownership', function () {
    ['owner' => $owner, 'shop' => $shop] = setupOwnerWithPlan();

    $this->actingAs($owner)
        ->put(route('shop.setup.store'), [
            'ownership_type' => 'franchisee',
            'franchise_brand_name' => 'CleanWave',
            'franchisor_name' => 'CleanWave Franchising Corporation',
            'location_mode' => 'single',
            'offers_pickup' => false,
            'offers_delivery' => false,
        ])
        ->assertRedirect(route('shop.dashboard'));

    $shop->refresh();
    expect($shop->ownership_type)->toBe('franchisee')
        ->and($shop->franchise_brand_name)->toBe('CleanWave')
        ->and($shop->franchisor_name)->toBe('CleanWave Franchising Corporation');
});

test('single location shop cannot access branch management', function () {
    ['owner' => $owner, 'shop' => $shop] = setupOwnerWithPlan('Premium');
    $shop->update([
        'location_mode' => 'single',
        'offers_pickup' => false,
        'offers_delivery' => false,
        'setup_completed_at' => now(),
    ]);

    $this->actingAs($owner)
        ->get(route('branch.index'))
        ->assertForbidden();
});

test('customer pickup request is rejected when shop pickup is disabled', function () {
    ['shop' => $shop] = setupOwnerWithPlan('Premium');
    $shop->update([
        'location_mode' => 'single',
        'offers_pickup' => false,
        'offers_delivery' => false,
        'setup_completed_at' => now(),
    ]);
    $customer = User::factory()->create(['role' => AccountType::Customer->value]);

    $this->actingAs($customer)
        ->post(route('user.orders.store'), [
            'shop_id' => $shop->id,
            'pickup_type' => 'pickup',
        ])
        ->assertForbidden();
});

test('shop cannot create deliveries when delivery is disabled', function () {
    ['owner' => $owner, 'shop' => $shop] = setupOwnerWithPlan('Premium');
    $shop->update([
        'location_mode' => 'single',
        'offers_pickup' => false,
        'offers_delivery' => false,
        'setup_completed_at' => now(),
    ]);

    $this->actingAs($owner)
        ->post(route('shop.logistics.riders.store'), [
            'name' => 'Test Rider',
        ])
        ->assertForbidden();
});

test('repeating setup updates preferences without changing completion time', function () {
    $this->travelTo(now()->startOfSecond());
    ['owner' => $owner, 'shop' => $shop] = setupOwnerWithPlan('Premium');

    $payload = [
        'location_mode' => 'single',
        'offers_pickup' => false,
        'offers_delivery' => false,
    ];

    $this->actingAs($owner)->put(route('shop.setup.store'), $payload);
    $completedAt = $shop->fresh()->setup_completed_at;
    $this->travel(10)->minutes();
    $this->actingAs($owner)->put(route('shop.setup.store'), [
        ...$payload,
        'offers_delivery' => true,
    ]);

    $shop->refresh();
    expect($shop->setup_completed_at->equalTo($completedAt))->toBeTrue()
        ->and($shop->offers_delivery)->toBeTrue();
});
