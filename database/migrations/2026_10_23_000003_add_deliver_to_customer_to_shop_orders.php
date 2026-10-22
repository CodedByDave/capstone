<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shop_orders', function (Blueprint $table) {
            // The customer drops the laundry off themselves but wants it brought back.
            $table->boolean('deliver_to_customer')->default(false)->after('pickup_type');
        });
    }

    public function down(): void
    {
        Schema::table('shop_orders', function (Blueprint $table) {
            $table->dropColumn('deliver_to_customer');
        });
    }
};
