<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** A cost per ml is a fraction of a peso (e.g. ₱0.016), which two decimals would round away. */
    public function up(): void
    {
        Schema::table('inventory', function (Blueprint $table) {
            $table->decimal('unit_price', 12, 4)->nullable()->change();
        });
        Schema::table('shop_order_supplies', function (Blueprint $table) {
            $table->decimal('unit_cost', 12, 4)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('inventory', function (Blueprint $table) {
            $table->decimal('unit_price', 10, 2)->nullable()->change();
        });
        Schema::table('shop_order_supplies', function (Blueprint $table) {
            $table->decimal('unit_cost', 10, 2)->nullable()->change();
        });
    }
};
