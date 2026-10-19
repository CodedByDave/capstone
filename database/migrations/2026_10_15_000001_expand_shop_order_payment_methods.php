<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE shop_orders MODIFY payment_method ENUM('cash','gcash','maya','card','grabpay','online') NULL DEFAULT NULL");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE shop_orders MODIFY payment_method ENUM('cash','gcash','maya','online') NULL DEFAULT NULL");
        }
    }
};
