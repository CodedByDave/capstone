<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            // The weight range (kg) one rider run can carry for a pickup or delivery. Null means no limit.
            $table->decimal('rider_min_weight_kg', 6, 2)->nullable();
            $table->decimal('rider_max_weight_kg', 6, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn(['rider_min_weight_kg', 'rider_max_weight_kg']);
        });
    }
};
