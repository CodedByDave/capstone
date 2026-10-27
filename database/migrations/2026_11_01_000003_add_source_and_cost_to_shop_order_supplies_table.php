<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shop_order_supplies', function (Blueprint $table) {
            // 'manual' = picked by staff and charged to the customer; 'service' = the service's own supplies.
            $table->string('source', 20)->default('manual')->after('unit');
            $table->decimal('unit_cost', 10, 2)->nullable()->after('source');
        });
    }

    public function down(): void
    {
        Schema::table('shop_order_supplies', function (Blueprint $table) {
            $table->dropColumn(['source', 'unit_cost']);
        });
    }
};
