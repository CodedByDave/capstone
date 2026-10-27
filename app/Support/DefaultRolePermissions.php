<?php

namespace App\Support;

/**
 * Preset RBAC permissions seeded for each default shop role.
 *
 * Module and action keys must match the permission middleware in routes/web.php
 * and the action map in resources/js/pages/shop/permission/Index.vue.
 * The "owner" role is intentionally absent: owners bypass permission checks.
 */
final class DefaultRolePermissions
{
    public const ROLES = ['owner', 'manager', 'cashier', 'washer', 'staff'];

    /**
     * @return array<string, array<string, list<string>>> role => module => actions
     */
    public static function presets(): array
    {
        return [
            // Runs the shop day to day, but cannot archive staff or finance records.
            'manager' => [
                'HRM' => ['view', 'create', 'update', 'import'],
                'Operations' => ['view', 'create', 'manage'],
                'Inventory Management' => ['view', 'create', 'update', 'archive'],
                'Finance Management' => ['view', 'create', 'update'],
                'Reports & Analytics' => ['view', 'export'],
                'Logistics' => ['view', 'create', 'update', 'archive'],
            ],
            // Front desk: takes orders, records payments and expenses, reads stock.
            // Operations "manage" is required for payments and status changes.
            'cashier' => [
                'Operations' => ['view', 'create', 'manage'],
                'Inventory Management' => ['view'],
                'Finance Management' => ['view', 'create'],
            ],
            // Production: sees the order queue and the supplies in stock.
            'washer' => [
                'Operations' => ['view'],
                'Inventory Management' => ['view'],
            ],
            // Minimal access: can only look at orders.
            'staff' => [
                'Operations' => ['view'],
            ],
        ];
    }

    /**
     * @return list<array{module: string, action: string}>
     */
    public static function forRole(string $role): array
    {
        $rows = [];

        foreach (self::presets()[$role] ?? [] as $module => $actions) {
            foreach ($actions as $action) {
                $rows[] = ['module' => $module, 'action' => $action];
            }
        }

        return $rows;
    }
}
