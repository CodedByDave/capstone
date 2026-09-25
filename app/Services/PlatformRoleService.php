<?php

namespace App\Services;

use App\Exceptions\ProtectedPlatformRoleException;
use App\Models\PlatformRole;
use App\Repositories\Contracts\PlatformRoleRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PlatformRoleService
{
    public function __construct(
        private readonly PlatformRoleRepositoryInterface $roles,
    ) {}

    public function managementData(): array
    {
        return [
            'roles' => $this->roles->allWithPermissions()
                ->map(fn (PlatformRole $role) => $this->rolePayload($role))
                ->values(),
            'permissionGroups' => collect($this->permissionGroups())
                ->map(fn (array $permissions, string $group) => [
                    'name' => $group,
                    'permissions' => collect($permissions)
                        ->map(fn (string $label, string $key) => compact('key', 'label'))
                        ->values(),
                ])
                ->values(),
        ];
    }

    public function permissionsForRole(?string $role): array
    {
        if ($role === 'super_admin') {
            return $this->permissionKeys();
        }

        return $role ? $this->roles->permissionsForSlug($role) : [];
    }

    public function canAccessOwnerPath(string $path): bool
    {
        $permission = $this->permissionForOwnerPath($path);

        return $permission !== null
            && in_array($permission, $this->permissionsForRole('owner'), true);
    }

    public function createRole(array $data): array
    {
        $role = $this->roles->create([
            'name' => trim($data['name']),
            'slug' => $data['role_slug'],
            'description' => $data['description'] ?? null,
            'is_system' => false,
        ]);

        return $this->rolePayload($this->roles->loadPermissions($role));
    }

    public function togglePermission(PlatformRole $role, string $permission): bool
    {
        if ($role->slug === 'super_admin') {
            throw new ProtectedPlatformRoleException('Super Admin permissions are locked.');
        }

        return DB::transaction(function () use ($role, $permission): bool {
            if ($this->roles->permissionExists($role, $permission)) {
                $this->roles->revokePermission($role, $permission);

                return false;
            }

            $this->roles->grantPermission($role, $permission);

            return true;
        });
    }

    public function deleteRole(PlatformRole $role): void
    {
        if ($role->is_system) {
            throw new ProtectedPlatformRoleException('System roles cannot be deleted.');
        }

        DB::transaction(fn () => $this->roles->delete($role));
    }

    private function rolePayload(PlatformRole $role): array
    {
        return [
            'public_id' => $role->public_id,
            'name' => $role->name,
            'slug' => $role->slug,
            'description' => $role->description,
            'is_system' => $role->is_system,
            'permissions' => $role->slug === 'super_admin'
                ? $this->permissionKeys()
                : $role->permissions->pluck('permission')->values()->all(),
        ];
    }

    private function permissionGroups(): array
    {
        return config('platform_permissions.groups', []);
    }

    private function permissionKeys(): array
    {
        return collect($this->permissionGroups())
            ->flatMap(fn (array $permissions) => array_keys($permissions))
            ->values()
            ->all();
    }

    private function permissionForOwnerPath(string $path): ?string
    {
        foreach (config('platform_permissions.owner_route_permissions', []) as $pattern => $permission) {
            if (Str::is($pattern, $path)) {
                return $permission;
            }
        }

        return null;
    }
}
