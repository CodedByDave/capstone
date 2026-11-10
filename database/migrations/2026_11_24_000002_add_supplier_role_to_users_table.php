<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['super_admin', 'owner', 'manager', 'staff', 'user', 'supplier'])
                ->default('user')
                ->change();
        });
    }

    public function down(): void
    {
        if (DB::table('users')->where('role', 'supplier')->exists()) {
            throw new RuntimeException('Supplier accounts still exist; remove them before rolling back this migration.');
        }

        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['super_admin', 'owner', 'manager', 'staff', 'user'])
                ->default('user')
                ->change();
        });
    }
};
