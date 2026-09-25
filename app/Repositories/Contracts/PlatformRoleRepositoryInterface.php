<?php

namespace App\Repositories\Contracts;

use App\Models\PlatformRole;
use Illuminate\Support\Collection;

interface PlatformRoleRepositoryInterface
{
    public function allWithPermissions(): Collection;

    public function permissionsForSlug(string $slug): array;

    public function create(array $attributes): PlatformRole;

    public function loadPermissions(PlatformRole $role): PlatformRole;

    public function permissionExists(PlatformRole $role, string $permission): bool;

    public function grantPermission(PlatformRole $role, string $permission): void;

    public function revokePermission(PlatformRole $role, string $permission): void;

    public function delete(PlatformRole $role): void;
}
