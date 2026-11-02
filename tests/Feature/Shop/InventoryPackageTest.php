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
        'package_unit' => 'bottle',
        'package_size' => 5000,
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

test('the purchase unit and its size are required', function () {
    $owner = packageShopOwner();

    $this->actingAs($owner)->post('/shop/inventory', packageItem(['package_unit' => '', 'package_size' => '']))
        ->assertSessionHasErrors(['package_unit', 'package_size']);
    $this->actingAs($owner)->post('/shop/inventory', packageItem(['package_unit' => 'bottle', 'package_size' => '']))
        ->assertSessionHasErrors('package_size');
    $this->actingAs($owner)->post('/shop/inventory', packageItem(['package_unit' => '', 'package_size' => 5000]))
        ->assertSessionHasErrors('package_unit');
    $this->actingAs($owner)->post('/shop/inventory', packageItem(['package_unit' => 'bottle', 'package_size' => 0]))
        ->assertSessionHasErrors('package_size');
});

test('starting stock with a cost is always booked as an expense', function () {
    $owner = packageShopOwner();

    $this->actingAs($owner)->post('/shop/inventory', packageItem([
        'quantity' => 2000,
        'unit_price' => 0.05,
    ]))->assertSessionHasNoErrors();

    $expense = App\Models\Expense::where('title', 'Restock: Liquid detergent')->firstOrFail();
    expect((float) $expense->amount)->toBe(100.0)
        ->and($expense->description)->toBe('2000 ml at ₱0.05 per ml');
});

test('starting stock without a cost books no expense', function () {
    $owner = packageShopOwner();

    $this->actingAs($owner)->post('/shop/inventory', packageItem([
        'unit_price' => 0,
    ]))->assertSessionHasNoErrors();

    expect(App\Models\Expense::count())->toBe(0);
});

test('a cost per ml keeps its fractions of a peso', function () {
    $owner = packageShopOwner();

    $this->actingAs($owner)->post('/shop/inventory', packageItem(['unit_price' => 0.016]))
        ->assertSessionHasNoErrors();

    expect((float) Inventory::firstOrFail()->unit_price)->toBe(0.016);
});

test('an add-on sets the per-unit selling price orders charge', function () {
    $owner = packageShopOwner();

    $this->actingAs($owner)->post('/shop/inventory', packageItem([
        'addon_enabled' => true,
        'addon_name' => 'Extra Fabric Conditioner',
        'addon_quantity' => 30,
        'addon_price' => 15,
    ]))->assertSessionHasNoErrors();

    $item = Inventory::firstOrFail();
    expect($item->addon_enabled)->toBeTrue()
        ->and($item->addon_name)->toBe('Extra Fabric Conditioner')
        ->and((float) $item->selling_price)->toBe(0.5);
});

test('a retail product sets the selling price per unit from its pack', function () {
    $owner = packageShopOwner();

    $this->actingAs($owner)->post('/shop/inventory', packageItem([
        'retail_enabled' => true,
        'retail_name' => 'Fabric Conditioner 1 L',
        'retail_pack_size' => 1000,
        'retail_price' => 150,
    ]))->assertSessionHasNoErrors();

    $item = Inventory::firstOrFail();
    expect($item->retail_enabled)->toBeTrue()
        ->and((float) $item->selling_price)->toBe(0.15);
});

test('the add-on price wins when both sales options are on', function () {
    $owner = packageShopOwner();

    $this->actingAs($owner)->post('/shop/inventory', packageItem([
        'addon_enabled' => true, 'addon_name' => 'Extra', 'addon_quantity' => 30, 'addon_price' => 15,
        'retail_enabled' => true, 'retail_name' => 'Bottle', 'retail_pack_size' => 1000, 'retail_price' => 150,
    ]))->assertSessionHasNoErrors();

    expect((float) Inventory::firstOrFail()->selling_price)->toBe(0.5);
});

test('an enabled sales option needs its details', function () {
    $owner = packageShopOwner();

    $this->actingAs($owner)->post('/shop/inventory', packageItem(['addon_enabled' => true]))
        ->assertSessionHasErrors(['addon_name', 'addon_quantity', 'addon_price']);
    $this->actingAs($owner)->post('/shop/inventory', packageItem(['retail_enabled' => true]))
        ->assertSessionHasErrors(['retail_name', 'retail_pack_size', 'retail_price']);
});

test('switching the sales options off clears the settings and the selling price', function () {
    $owner = packageShopOwner();
    $this->actingAs($owner)->post('/shop/inventory', packageItem([
        'addon_enabled' => true, 'addon_name' => 'Extra', 'addon_quantity' => 30, 'addon_price' => 15,
    ]));
    $item = Inventory::firstOrFail();

    $this->actingAs($owner)->put("/shop/inventory/{$item->id}", [
        'name' => $item->name, 'unit' => 'ml', 'package_unit' => 'bottle', 'package_size' => 5000, 'min_stock' => 2000, 'status' => 'active',
        'addon_enabled' => false,
    ])->assertSessionHasNoErrors();

    $item->refresh();
    expect($item->addon_enabled)->toBeFalse()
        ->and($item->addon_price)->toBeNull()
        ->and($item->selling_price)->toBeNull();
});
