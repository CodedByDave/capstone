<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            // Per-weekday hours: {"1": {"opens_at": "10:00", "closes_at": "17:00"}, ...}.
            // A day missing here falls back to the shop-wide opens_at / closes_at.
            $table->json('daily_hours')->nullable()->after('operating_days');
        });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn('daily_hours');
        });
    }
};
