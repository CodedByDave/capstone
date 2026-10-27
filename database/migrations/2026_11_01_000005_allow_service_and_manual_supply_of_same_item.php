<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * An order can now hold one row per supply source, so staff can add by hand an item the
     * service already uses. The new index starts with order_id, so the foreign key keeps an index
     * while the old one is dropped.
     */
    public function up(): void
    {
        Schema::table('shop_order_supplies', function (Blueprint $table) {
            $table->unique(['order_id', 'inventory_id', 'source'], 'shop_order_supplies_order_inventory_source_unique');
        });

        Schema::table('shop_order_supplies', function (Blueprint $table) {
            $table->dropUnique(['order_id', 'inventory_id']);
        });
    }

    public function down(): void
    {
        Schema::table('shop_order_supplies', function (Blueprint $table) {
            $table->unique(['order_id', 'inventory_id']);
        });

        Schema::table('shop_order_supplies', function (Blueprint $table) {
            $table->dropUnique('shop_order_supplies_order_inventory_source_unique');
        });
    }
};
