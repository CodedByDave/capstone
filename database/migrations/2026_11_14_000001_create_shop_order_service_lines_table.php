<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Extra service lines on an order. The order keeps its first service on its own columns; each
     * additional service (for example a per-piece comforter next to a per-kg wash & fold) is a row
     * here with its own copy of the rate, like the order keeps for its main service.
     */
    public function up(): void
    {
        Schema::create('shop_order_service_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_order_id')->constrained('shop_orders')->cascadeOnDelete();
            $table->foreignId('service_id')->nullable()->constrained('services_and_pricing')->nullOnDelete();
            $table->string('service_name');
            $table->enum('pricing_model', ['per_kg', 'per_bundle', 'per_piece'])->default('per_kg');
            $table->decimal('price_per_kg', 10, 2)->nullable();
            $table->decimal('bundle_weight_kg', 8, 2)->nullable();
            $table->decimal('bundle_price', 10, 2)->nullable();
            $table->decimal('price_per_piece', 10, 2)->nullable();
            $table->decimal('weight_kg', 8, 2)->nullable();
            $table->unsignedInteger('bundle_quantity')->nullable();
            $table->unsignedSmallInteger('piece_count')->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->timestamps();

            $table->index('shop_order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_order_service_lines');
    }
};
