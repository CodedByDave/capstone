<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('shop_orders', function (Blueprint $table) {
            $table->enum('pricing_model', ['per_kg', 'per_bundle'])->default('per_kg')->after('pickup_type');
            $table->decimal('bundle_weight_kg', 8, 2)->nullable()->after('price_per_kg');
            $table->decimal('bundle_price', 10, 2)->nullable()->after('bundle_weight_kg');
            $table->integer('bundle_quantity')->nullable()->after('bundle_price');

            // Make weight fields nullable for bundle orders
            $table->decimal('estimated_weight_kg', 6, 2)->nullable()->change();
            $table->decimal('actual_weight_kg', 6, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('shop_orders', function (Blueprint $table) {
            $table->dropColumn(['pricing_model', 'bundle_weight_kg', 'bundle_price', 'bundle_quantity']);
            $table->decimal('estimated_weight_kg', 6, 2)->nullable(false)->change();
            $table->decimal('actual_weight_kg', 6, 2)->nullable(false)->change();
        });
    }
};
