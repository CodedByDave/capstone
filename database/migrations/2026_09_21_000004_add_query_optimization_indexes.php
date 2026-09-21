<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['user_id', 'status', 'id'], 'orders_user_status_id_idx');
            $table->index(['status', 'expires_at'], 'orders_status_expiry_idx');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->index(['status', 'paid_at'], 'payments_status_paid_at_idx');
            $table->index(['order_id', 'status'], 'payments_order_status_idx');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->index(['shop_id', 'date', 'status'], 'attendance_shop_date_status_idx');
        });

        Schema::table('payrolls', function (Blueprint $table) {
            $table->index(['shop_id', 'status', 'period_start'], 'payroll_shop_status_period_idx');
        });

        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->index(['shop_id', 'created_at', 'type'], 'movement_shop_date_type_idx');
        });

        Schema::table('shop_orders', function (Blueprint $table) {
            $table->index(['shop_id', 'status', 'created_at'], 'shop_order_status_date_idx');
            $table->index(['user_id', 'created_at'], 'shop_order_user_date_idx');
        });

        Schema::table('deliveries', function (Blueprint $table) {
            $table->index(['shop_id', 'status'], 'delivery_shop_status_idx');
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->index(['shop_id', 'created_at'], 'activity_shop_date_idx');
            $table->index(['module', 'action', 'created_at'], 'activity_module_action_date_idx');
        });

        Schema::table('login_logs', function (Blueprint $table) {
            $table->index(['email', 'status', 'logged_at'], 'login_email_status_date_idx');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_user_status_id_idx');
            $table->dropIndex('orders_status_expiry_idx');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('payments_status_paid_at_idx');
            $table->dropIndex('payments_order_status_idx');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex('attendance_shop_date_status_idx');
        });

        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropIndex('payroll_shop_status_period_idx');
        });

        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->dropIndex('movement_shop_date_type_idx');
        });

        Schema::table('shop_orders', function (Blueprint $table) {
            $table->dropIndex('shop_order_status_date_idx');
            $table->dropIndex('shop_order_user_date_idx');
        });

        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropIndex('delivery_shop_status_idx');
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex('activity_shop_date_idx');
            $table->dropIndex('activity_module_action_date_idx');
        });

        Schema::table('login_logs', function (Blueprint $table) {
            $table->dropIndex('login_email_status_date_idx');
        });
    }
};
