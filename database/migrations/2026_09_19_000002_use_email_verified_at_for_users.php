<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->where('is_verified', true)
            ->whereNull('email_verified_at')
            ->update(['email_verified_at' => now()]);

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_verified');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_verified')->default(false);
        });

        DB::table('users')
            ->whereNotNull('email_verified_at')
            ->update(['is_verified' => true]);
    }
};
