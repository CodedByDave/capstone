<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            $table->ulid('public_id')->nullable()->unique()->after('id');
            $table->decimal('destination_lat', 10, 7)->nullable()->after('delivery_address');
            $table->decimal('destination_lng', 10, 7)->nullable()->after('destination_lat');
            $table->decimal('distance_km', 8, 2)->nullable()->after('destination_lng');
            $table->decimal('delivery_fee', 10, 2)->default(0)->after('distance_km');
            $table->decimal('rider_lat', 10, 7)->nullable()->after('delivery_fee');
            $table->decimal('rider_lng', 10, 7)->nullable()->after('rider_lat');
            $table->timestamp('rider_located_at')->nullable()->after('rider_lng');
        });

        DB::table('deliveries')
            ->select('id')
            ->whereNull('public_id')
            ->orderBy('id')
            ->chunkById(100, function ($deliveries): void {
                foreach ($deliveries as $delivery) {
                    DB::table('deliveries')
                        ->where('id', $delivery->id)
                        ->update(['public_id' => (string) Str::ulid()]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropUnique(['public_id']);
            $table->dropColumn([
                'public_id',
                'destination_lat',
                'destination_lng',
                'distance_km',
                'delivery_fee',
                'rider_lat',
                'rider_lng',
                'rider_located_at',
            ]);
        });
    }
};
