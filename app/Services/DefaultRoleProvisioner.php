<?php

namespace App\Services;

use App\Repositories\ShopRoleRepository;
use App\Support\DefaultRolePermissions;

class DefaultRoleProvisioner
{
    public function __construct(protected ShopRoleRepository $roles) {}

    /**
     * Create the default roles for a shop and apply their preset permissions.
     */
    public function provision(int $shopId): void
    {
        foreach (DefaultRolePermissions::ROLES as $role) {
            $this->provisionRole($shopId, $role);
        }
    }

    /**
     * Create the rider role for a shop that offers pickup or delivery, so the owner can
     * hire riders right away. Turning logistics off later keeps the role and its riders.
     */
    public function provisionLogisticsRoles(int $shopId, bool $offersPickup, bool $offersDelivery): void
    {
        if ($offersPickup || $offersDelivery) {
            $this->provisionRole($shopId, DefaultRolePermissions::RIDER_ROLE);
        }
    }

    /**
     * Ensure a role exists and, when it has no permissions yet, apply its preset.
     * Roles that already have permissions are left alone so owner customisations survive.
     */
    public function provisionRole(int $shopId, string $role): void
    {
        $this->roles->firstOrCreateDefault($shopId, $role);

        if ($this->roles->hasPermissions($shopId, $role)) {
            return;
        }

        $this->roles->grant($shopId, $role, DefaultRolePermissions::forRole($role));
    }
}
