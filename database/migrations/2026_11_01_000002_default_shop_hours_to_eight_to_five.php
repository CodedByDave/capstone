<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->boolean('is_open_24_7')->default(false)->change();
            $table->time('opens_at')->nullable()->default('08:00:00')->change();
            $table->time('closes_at')->nullable()->default('17:00:00')->change();
        });

        // Shops that never chose their hours get the new default.
        DB::table('shops')->whereNull('opens_at')->update(['is_open_24_7' => false, 'opens_at' => '08:00:00', 'closes_at' => '17:00:00']);
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->boolean('is_open_24_7')->default(true)->change();
            $table->time('opens_at')->nullable()->default(null)->change();
            $table->time('closes_at')->nullable()->default(null)->change();
        });
    }
};
