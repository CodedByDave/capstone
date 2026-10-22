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
            // Set when the owner explicitly saves delivery pricing. A shop that offers delivery
            // must have it, so a delivery can never go out without a fee being decided.
            $table->timestamp('delivery_pricing_set_at')->nullable()->after('delivery_max_km');
        });

        // Shops that already charge something have effectively set their pricing.
        DB::table('shops')
            ->where('offers_delivery', true)
            ->where(function ($query) {
                $query->where('delivery_fixed_fee', '>', 0)
                    ->orWhere('delivery_base_fee', '>', 0)
                    ->orWhere('delivery_per_km_rate', '>', 0)
                    ->orWhere('delivery_min_fee', '>', 0);
            })
            ->update(['delivery_pricing_set_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn('delivery_pricing_set_at');
        });
    }
};
