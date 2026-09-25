<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_roles', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->string('name', 50);
            $table->string('slug', 50)->unique();
            $table->string('description', 255)->nullable();
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        Schema::create('platform_role_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('platform_role_id')->constrained()->cascadeOnDelete();
            $table->string('permission', 100);
            $table->timestamps();
            $table->unique(['platform_role_id', 'permission']);
        });

        $now = now();
        DB::table('platform_roles')->insert([
            [
                'public_id' => (string) str()->ulid(),
                'name' => 'Super Admin',
                'slug' => 'super_admin',
                'description' => 'Complete platform access. This role is locked.',
                'is_system' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'public_id' => (string) str()->ulid(),
                'name' => 'Shop Owner',
                'slug' => 'owner',
                'description' => 'Runs a shop and manages its subscribed modules.',
                'is_system' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'public_id' => (string) str()->ulid(),
                'name' => 'User',
                'slug' => 'user',
                'description' => 'Customer access for browsing shops and managing orders.',
                'is_system' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $roleIds = DB::table('platform_roles')->pluck('id', 'slug');
        $defaults = [
            'owner' => [
                'account.view', 'account.update', 'marketplace.view',
                'orders.create', 'orders.view_own', 'payments.manage_own',
                'shop.dashboard', 'shop.staff', 'shop.operations',
                'shop.inventory', 'shop.finance', 'shop.analytics',
                'shop.settings',
            ],
            'user' => [
                'account.view', 'account.update', 'marketplace.view',
                'orders.create', 'orders.view_own', 'payments.manage_own',
            ],
        ];

        foreach ($defaults as $slug => $permissions) {
            foreach ($permissions as $permission) {
                DB::table('platform_role_permissions')->insert([
                    'platform_role_id' => $roleIds[$slug],
                    'permission' => $permission,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_role_permissions');
        Schema::dropIfExists('platform_roles');
    }
};
