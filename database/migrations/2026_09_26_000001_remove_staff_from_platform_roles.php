<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('platform_roles')->where('slug', 'staff')->delete();
    }

    public function down(): void
    {
        DB::table('platform_roles')->insertOrIgnore([
            'public_id' => (string) str()->ulid(),
            'name' => 'Staff',
            'slug' => 'staff',
            'description' => 'Uses only the shop capabilities assigned to them.',
            'is_system' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
};
