<?php

use App\Models\RolePermission;
use App\Models\Shop;
use App\Support\DefaultRolePermissions;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Give existing shops the preset permissions for default roles that have none yet.
     * Roles the owner has already configured are left untouched.
     */
    public function up(): void
    {
        Shop::query()->select('id')->each(function (Shop $shop) {
            foreach (DefaultRolePermissions::ROLES as $role) {
                $alreadyConfigured = RolePermission::where('shop_id', $shop->id)->where('role', $role)->exists();

                if ($alreadyConfigured) {
                    continue;
                }

                foreach (DefaultRolePermissions::forRole($role) as $permission) {
                    RolePermission::firstOrCreate([
                        'shop_id' => $shop->id,
                        'role' => $role,
                        ...$permission,
                    ]);
                }
            }
        });
    }

    public function down(): void
    {
        // Seeded permissions are indistinguishable from owner-edited ones; nothing to undo.
    }
};
