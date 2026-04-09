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
        Schema::create('shop_order_supplies', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            $table->foreignId('inventory_id')
                ->constrained('inventory')
                ->restrictOnDelete();

            // How much of this supply was used for this order
            $table->decimal('quantity_used', 8, 2)->default(1.00);

            // Snapshot of the unit at time of order (in case inventory is edited later)
            $table->string('unit')->nullable();

            // Link to the inventory_movement record created when this supply was consumed
            $table->foreignId('inventory_movement_id')
                ->nullable()
                ->constrained('inventory_movements')
                ->nullOnDelete();

            $table->timestamps();

            // A supply item should only appear once per order
            $table->unique(['order_id', 'inventory_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_order_supplies');
    }
};
