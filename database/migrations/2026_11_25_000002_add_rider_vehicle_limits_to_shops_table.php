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
            // The load capacity of each vehicle type, keyed by type: {"Motorcycle": {"min": 3, "max": 15}}.
            // rider_min_weight_kg / rider_max_weight_kg keep holding the capacity of the vehicle in use.
            $table->json('rider_vehicle_limits')->nullable()->after('rider_vehicle_types');
        });

        // The current limit belongs to the vehicle each shop uses today.
        DB::table('shops')
            ->where(fn ($q) => $q->whereNotNull('rider_min_weight_kg')->orWhereNotNull('rider_max_weight_kg'))
            ->orderBy('id')
            ->each(function ($shop) {
                DB::table('shops')->where('id', $shop->id)->update([
                    'rider_vehicle_limits' => json_encode([
                        ($shop->rider_vehicle_type ?: 'Motorcycle') => [
                            'min' => $shop->rider_min_weight_kg !== null ? (float) $shop->rider_min_weight_kg : null,
                            'max' => $shop->rider_max_weight_kg !== null ? (float) $shop->rider_max_weight_kg : null,
                        ],
                    ]),
                ]);
            });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn('rider_vehicle_limits');
        });
    }
};
