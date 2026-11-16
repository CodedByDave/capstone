<?php

namespace App\Repositories;

use App\Models\Module;
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

    /** @return list<string> The names of the shop's roles, lower-cased. */
    public function roleNames(int $shopId): array
    {
        return ShopRole::where('shop_id', $shopId)->pluck('name')->map(fn ($n) => mb_strtolower($n))->all();
    }

    /** @return list<string> The platform's module names. */
    public function moduleNames(): array
    {
        return Module::query()->pluck('name')->all();
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
