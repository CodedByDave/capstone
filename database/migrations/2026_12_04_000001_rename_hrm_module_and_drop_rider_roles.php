<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const RIDER_ROLES = ['rider', 'driver'];

    public function up(): void
    {
        // The HRM module is now called Staff Management, in the plan modules and in every role's permissions.
        DB::table('modules')->where('name', 'HRM')->update(['name' => 'Staff Management']);
        DB::table('role_permissions')->where('module', 'HRM')->update(['module' => 'Staff Management']);

        // Riders are staff who work from the driver app, not a role on the permissions page. Their
        // access follows their position, so the rider role and its assignments are removed.
        DB::table('employee_roles')->whereIn(DB::raw('LOWER(role)'), self::RIDER_ROLES)->delete();
        DB::table('role_permissions')->whereIn(DB::raw('LOWER(role)'), self::RIDER_ROLES)->delete();
        DB::table('shop_roles')->whereIn(DB::raw('LOWER(name)'), self::RIDER_ROLES)->delete();
    }

    public function down(): void
    {
        DB::table('modules')->where('name', 'Staff Management')->update(['name' => 'HRM']);
        DB::table('role_permissions')->where('module', 'Staff Management')->update(['module' => 'HRM']);
    }
};
