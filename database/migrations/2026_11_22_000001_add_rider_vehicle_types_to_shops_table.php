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
            // The vehicle the shop's riders use, and the choices the owner can pick from. A null list means Motorcycle only.
            $table->string('rider_vehicle_type', 40)->default('Motorcycle');
            $table->json('rider_vehicle_types')->nullable();
        });

        DB::table('riders')->whereNull('vehicle_type')->update(['vehicle_type' => 'Motorcycle']);
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn(['rider_vehicle_type', 'rider_vehicle_types']);
        });
    }
};
