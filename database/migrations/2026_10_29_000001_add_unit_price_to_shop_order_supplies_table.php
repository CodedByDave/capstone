<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shop_order_supplies', function (Blueprint $table) {
            // The selling price when the supply was added, so later price changes never alter an
            // issued receipt. Null for supplies added before this was recorded.
            $table->decimal('unit_price', 10, 2)->nullable()->after('unit');
        });
    }

    public function down(): void
    {
        Schema::table('shop_order_supplies', function (Blueprint $table) {
            $table->dropColumn('unit_price');
        });
    }
};
