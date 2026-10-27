<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adds a third pricing model: a fixed price per item (comforters, shoes, bags). The order keeps
     * its own copy of the rate and the item count, like it does for the other models.
     */
    public function up(): void
    {
        Schema::table('services_and_pricing', function (Blueprint $table) {
            $table->enum('pricing_model', ['per_kg', 'per_bundle', 'per_piece'])->default('per_kg')->change();
            $table->decimal('price_per_piece', 10, 2)->nullable()->after('bundle_price');
        });

        Schema::table('shop_orders', function (Blueprint $table) {
            $table->enum('pricing_model', ['per_kg', 'per_bundle', 'per_piece'])->default('per_kg')->change();
            $table->decimal('price_per_piece', 10, 2)->nullable()->after('bundle_price');
            $table->unsignedSmallInteger('piece_count')->nullable()->after('price_per_piece');
        });
    }

    public function down(): void
    {
        Schema::table('shop_orders', function (Blueprint $table) {
            $table->dropColumn(['price_per_piece', 'piece_count']);
            $table->enum('pricing_model', ['per_kg', 'per_bundle'])->default('per_kg')->change();
        });

        Schema::table('services_and_pricing', function (Blueprint $table) {
            $table->dropColumn('price_per_piece');
            $table->enum('pricing_model', ['per_kg', 'per_bundle'])->default('per_kg')->change();
        });
    }
};
