<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            // Open around the clock, or only between opens_at and closes_at (which may pass midnight).
            $table->boolean('is_open_24_7')->default(true);
            $table->time('opens_at')->nullable();
            $table->time('closes_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn(['is_open_24_7', 'opens_at', 'closes_at']);
        });
    }
};
