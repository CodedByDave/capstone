<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('plan_name')->default('Standard')->after('subscription_plan'); // Basic / Standard / Premium
            $table->unsignedInteger('billing_months')->default(1)->after('plan_name');    // 1 / 12 / 24 / 48

            // Replace the enum — it can't hold "12 months" style values
            $table->dropColumn('subscription_plan');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['plan_name', 'billing_months']);
            $table->enum('subscription_plan', ['monthly', 'annually'])->default('monthly');
        });
    }
};
