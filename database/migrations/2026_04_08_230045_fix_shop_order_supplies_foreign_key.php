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
        Schema::table('shop_order_supplies', function (Blueprint $table) {
            // Drop the wrong foreign key
            $table->dropForeign(['order_id']);

            // Add the correct one pointing to shop_orders
            $table->foreign('order_id')
                ->references('id')
                ->on('shop_orders')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('shop_order_supplies', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->foreign('order_id')
                ->references('id')
                ->on('orders')
                ->cascadeOnDelete();
        });
    }
};
