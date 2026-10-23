<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shop_orders', function (Blueprint $table) {
            // Where the customer is, as pinned when ordering: the rider's destination on a pickup run.
            // Both are null when the customer typed an address that could not be located.
            $table->decimal('customer_lat', 10, 7)->nullable()->after('customer_address');
            $table->decimal('customer_lng', 10, 7)->nullable()->after('customer_lat');
        });
    }

    public function down(): void
    {
        Schema::table('shop_orders', function (Blueprint $table) {
            $table->dropColumn(['customer_lat', 'customer_lng']);
        });
    }
};
