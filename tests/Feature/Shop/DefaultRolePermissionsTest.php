<?php

use App\Models\RolePermission;
use App\Models\Shop;
use App\Models\User;
use App\Services\DefaultRoleProvisioner;
use App\Support\DefaultRolePermissions;

function provisionedShop(): Shop
{
    $owner = User::factory()->create();
    $shop = Shop::create([
        'owner_id' => $owner->id,
        'shop_name' => 'Preset Laundry',
        'phone' => '09171234567',
        'municipality' => 'Imus',
        'barangay' => 'Poblacion',
        'postal_code' => '4103',
        'status' => 'active',
    ]);
    app(DefaultRoleProvisioner::class)->provision($shop->id);

    return $shop;
}

it('seeds preset permissions for each default role', function () {
    $shop = provisionedShop();

    foreach (['manager', 'cashier', 'washer', 'staff'] as $role) {
        expect(RolePermission::where('shop_id', $shop->id)->where('role', $role)->count())
            ->toBe(count(DefaultRolePermissions::forRole($role)));
    }

    expect(RolePermission::where('shop_id', $shop->id)->where('role', 'owner')->exists())->toBeFalse();
});

it('keeps least privilege between roles', function () {
    $shop = provisionedShop();
    $has = fn (string $role, string $module, string $action) => RolePermission::where([
        'shop_id' => $shop->id, 'role' => $role, 'module' => $module, 'action' => $action,
    ])->exists();

    expect($has('cashier', 'Operations', 'manage'))->toBeTrue()
        ->and($has('cashier', 'HRM', 'view'))->toBeFalse()
        ->and($has('washer', 'Operations', 'manage'))->toBeFalse()
        ->and($has('staff', 'Finance Management', 'view'))->toBeFalse()
        ->and($has('manager', 'HRM', 'archive'))->toBeFalse();
});

it('does not overwrite permissions the owner already customised', function () {
    $shop = provisionedShop();
    RolePermission::where('shop_id', $shop->id)->where('role', 'staff')->delete();
    RolePermission::create(['shop_id' => $shop->id, 'role' => 'staff', 'module' => 'Logistics', 'action' => 'view']);

    app(DefaultRoleProvisioner::class)->provision($shop->id);

    expect(RolePermission::where('shop_id', $shop->id)->where('role', 'staff')->pluck('module')->all())
        ->toBe(['Logistics']);
});
