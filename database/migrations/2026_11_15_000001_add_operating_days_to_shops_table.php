<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            // Weekdays the shop opens on (0 = Sunday ... 6 = Saturday). Null means every day.
            $table->json('operating_days')->nullable()->after('closes_at');
        });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn('operating_days');
        });
    }
};
