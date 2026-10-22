<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->string('delivery_fee_mode', 20)->default('fixed')->after('offers_delivery');
            $table->decimal('delivery_fixed_fee', 10, 2)->default(0)->after('delivery_fee_mode');
            $table->decimal('delivery_base_fee', 10, 2)->default(0)->after('delivery_fixed_fee');
            $table->decimal('delivery_per_km_rate', 10, 2)->default(0)->after('delivery_base_fee');
            $table->decimal('delivery_free_km', 8, 2)->default(0)->after('delivery_per_km_rate');
            $table->decimal('delivery_min_fee', 10, 2)->default(0)->after('delivery_free_km');
            // Null means the shop delivers at any distance.
            $table->decimal('delivery_max_km', 8, 2)->nullable()->after('delivery_min_fee');
        });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn([
                'delivery_fee_mode',
                'delivery_fixed_fee',
                'delivery_base_fee',
                'delivery_per_km_rate',
                'delivery_free_km',
                'delivery_min_fee',
                'delivery_max_km',
            ]);
        });
    }
};
