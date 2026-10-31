<?php

use App\Enums\AccountType;
use App\Http\Middleware\EnforceOwnerPlatformPermissions;
use App\Http\Middleware\EnsureCurrentBusinessAgreementAccepted;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\Shop;
use App\Models\User;

beforeEach(function () {
    $this->withoutMiddleware([
        EnsureCurrentBusinessAgreementAccepted::class,
        EnforceOwnerPlatformPermissions::class,
    ]);
});

function packageShopOwner(): User
{
    $owner = User::factory()->create(['role' => AccountType::ShopOwner->value]);
    Shop::create([
        'owner_id' => $owner->id,
        'shop_name' => 'Package Laundry',
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Poblacion',
        'postal_code' => '4103',
        'status' => 'active',
        'setup_completed_at' => now(),
    ]);
    Order::create([
        'user_id' => $owner->id,
        'shop_name' => 'Package Laundry',
        'owner_name' => $owner->name,
        'email' => $owner->email,
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Poblacion',
        'postal_code' => '4103',
        'plan_name' => 'Premium',
        'billing_months' => 1,
        'is_trial' => false,
        'total_price' => 2239,
        'status' => 'paid',
        'expires_at' => now()->addMonth(),
    ]);

    return $owner->fresh();
}

function packageItem(array $overrides = []): array
{
    return array_merge([
        'name' => 'Liquid detergent',
        'unit' => 'ml',
        'quantity' => 15000,
        'min_stock' => 2000,
        'status' => 'active',
    ], $overrides);
}

test('an item bought in packages stores its package name and size', function () {
    $owner = packageShopOwner();

    $this->actingAs($owner)->post('/shop/inventory', packageItem([
        'package_unit' => 'bottle',
        'package_size' => 5000,
    ]))->assertSessionHasNoErrors();

    $item = Inventory::where('name', 'Liquid detergent')->firstOrFail();
    expect($item->quantity)->toBe(15000)
        ->and($item->package_unit)->toBe('bottle')
        ->and($item->package_size)->toBe(5000);
});

test('an item without packages still saves as before', function () {
    $owner = packageShopOwner();

    $this->actingAs($owner)->post('/shop/inventory', packageItem())->assertSessionHasNoErrors();

    $item = Inventory::where('name', 'Liquid detergent')->firstOrFail();
    expect($item->package_unit)->toBeNull()->and($item->package_size)->toBeNull();
});

test('the package name and size must be given together and the size must be positive', function () {
    $owner = packageShopOwner();

    $this->actingAs($owner)->post('/shop/inventory', packageItem(['package_unit' => 'bottle']))
        ->assertSessionHasErrors('package_size');
    $this->actingAs($owner)->post('/shop/inventory', packageItem(['package_size' => 5000]))
        ->assertSessionHasErrors('package_unit');
    $this->actingAs($owner)->post('/shop/inventory', packageItem(['package_unit' => 'bottle', 'package_size' => 0]))
        ->assertSessionHasErrors('package_size');
});
