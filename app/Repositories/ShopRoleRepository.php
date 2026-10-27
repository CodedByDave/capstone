<?php

namespace App\Repositories;

use App\Models\RolePermission;
use App\Models\ShopRole;

class ShopRoleRepository
{
    public function firstOrCreateDefault(int $shopId, string $role): ShopRole
    {
        return ShopRole::firstOrCreate(
            ['shop_id' => $shopId, 'name' => $role],
            ['is_default' => true]
        );
    }

    public function hasPermissions(int $shopId, string $role): bool
    {
        return RolePermission::where('shop_id', $shopId)->where('role', $role)->exists();
    }

    /**
     * @param  list<array{module: string, action: string}>  $permissions
     */
    public function grant(int $shopId, string $role, array $permissions): void
    {
        foreach ($permissions as $permission) {
            RolePermission::firstOrCreate([
                'shop_id' => $shopId,
                'role' => $role,
                'module' => $permission['module'],
                'action' => $permission['action'],
            ]);
        }
    }
}
