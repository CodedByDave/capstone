<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Customers can buy a shop's retail products, either with a laundry order or on their own.
 *
 * - `shop_orders.order_type`: 'laundry' (every existing order) or 'products' (retail products only,
 *   collected at the shop). A products order has no laundry service, so `service_id` becomes nullable.
 * - `shop_order_supplies.unit_price` is widened to eight decimals: a ₱149 bottle of 1,000 ml is
 *   ₱0.149 per ml, which two decimals would store as ₱0.15 and show the line as ₱150.
 * - `shop_order_supplies.label` keeps the name the customer bought the item under
 *   (e.g. "Softener 1L bottle × 2"), since the item itself keeps its stock name.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shop_orders', function (Blueprint $table) {
            $table->string('order_type', 20)->default('laundry')->after('order_source')->index();
            $table->unsignedBigInteger('service_id')->nullable()->change();
        });

        Schema::table('shop_order_supplies', function (Blueprint $table) {
            $table->decimal('unit_price', 16, 8)->nullable()->change();
            $table->string('label', 150)->nullable()->after('unit');
        });
    }

    public function down(): void
    {
        Schema::table('shop_order_supplies', function (Blueprint $table) {
            $table->dropColumn('label');
            $table->decimal('unit_price', 10, 2)->nullable()->change();
        });

        // A products order has no service, so the column can only become required again when none exist.
        $hasProductOrders = DB::table('shop_orders')->whereNull('service_id')->exists();

        Schema::table('shop_orders', function (Blueprint $table) use ($hasProductOrders) {
            $table->dropIndex(['order_type']);
            $table->dropColumn('order_type');

            if (! $hasProductOrders) {
                $table->unsignedBigInteger('service_id')->nullable(false)->change();
            }
        });
    }
};
