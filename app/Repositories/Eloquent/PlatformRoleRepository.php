<?php

namespace App\Repositories\Eloquent;

use App\Models\PlatformRole;
use App\Repositories\Contracts\PlatformRoleRepositoryInterface;
use Illuminate\Support\Collection;

class PlatformRoleRepository implements PlatformRoleRepositoryInterface
{
    public function allWithPermissions(): Collection
    {
        return PlatformRole::query()
            ->with('permissions:id,platform_role_id,permission')
            ->orderByDesc('is_system')
            ->orderBy('id')
            ->get();
    }

    public function permissionsForSlug(string $slug): array
    {
        $role = PlatformRole::query()
            ->where('slug', $slug)
            ->with('permissions:id,platform_role_id,permission')
            ->first();

        return $role?->permissions->pluck('permission')->values()->all() ?? [];
    }

    public function create(array $attributes): PlatformRole
    {
        return PlatformRole::create($attributes);
    }

    public function loadPermissions(PlatformRole $role): PlatformRole
    {
        return $role->load('permissions:id,platform_role_id,permission');
    }

    public function permissionExists(PlatformRole $role, string $permission): bool
    {
        return $role->permissions()->where('permission', $permission)->exists();
    }

    public function grantPermission(PlatformRole $role, string $permission): void
    {
        $role->permissions()->create(['permission' => $permission]);
    }

    public function revokePermission(PlatformRole $role, string $permission): void
    {
        $role->permissions()->where('permission', $permission)->delete();
    }

    public function delete(PlatformRole $role): void
    {
        $role->delete();
    }
}
