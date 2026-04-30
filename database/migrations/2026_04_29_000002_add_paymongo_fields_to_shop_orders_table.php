<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Extend payment_method enum to include 'online' (PayMongo-processed)
        DB::statement("ALTER TABLE shop_orders MODIFY payment_method ENUM('cash','gcash','maya','online') NULL DEFAULT NULL");

        Schema::table('shop_orders', function (Blueprint $table) {
            $table->string('paymongo_session_id')->nullable()->after('paid_at');
            $table->string('paymongo_payment_id')->nullable()->after('paymongo_session_id');
        });
    }

    public function down(): void
    {
        Schema::table('shop_orders', function (Blueprint $table) {
            $table->dropColumn(['paymongo_session_id', 'paymongo_payment_id']);
        });

        DB::statement("ALTER TABLE shop_orders MODIFY payment_method ENUM('cash','gcash','maya') NULL DEFAULT NULL");
    }
};
