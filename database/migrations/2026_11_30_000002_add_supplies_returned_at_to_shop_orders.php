<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cancelling an order now puts its add-ons and retail products back in stock right away. This
 * marks the orders that did, so archiving them does not return the stock a second time and
 * reopening them takes it again. Orders cancelled before this stay unmarked: their stock still
 * comes back when they are archived, as it always did.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shop_orders', function (Blueprint $table) {
            $table->timestamp('supplies_returned_at')->nullable()->after('completed_at');
        });
    }

    public function down(): void
    {
        Schema::table('shop_orders', function (Blueprint $table) {
            $table->dropColumn('supplies_returned_at');
        });
    }
};
