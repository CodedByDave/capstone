<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shop_order_payments', function (Blueprint $table) {
            // Cash handed over at the counter and the change given back; `amount` stays what was applied to the bill.
            $table->decimal('amount_tendered', 10, 2)->nullable()->after('amount');
            $table->decimal('change_given', 10, 2)->nullable()->after('amount_tendered');
        });
    }

    public function down(): void
    {
        Schema::table('shop_order_payments', function (Blueprint $table) {
            $table->dropColumn(['amount_tendered', 'change_given']);
        });
    }
};
