<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            // How the driver app shows earnings: none, the delivery fee per run, or a flat daily rate.
            $table->string('driver_pay_mode', 20)->default('none');
            $table->decimal('driver_daily_rate', 10, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn(['driver_pay_mode', 'driver_daily_rate']);
        });
    }
};
