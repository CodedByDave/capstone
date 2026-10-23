<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shop_orders', function (Blueprint $table) {
            // When work started, so a due time can be worked out from the service's estimated hours.
            $table->timestamp('in_progress_at')->nullable()->after('estimated_completion_at');
            // Set once the shop has been told the order is overdue, so the reminder is sent once.
            $table->timestamp('overdue_notified_at')->nullable()->after('in_progress_at');

            $table->index(['status', 'estimated_completion_at'], 'shop_orders_status_estimated_completion_index');
        });
    }

    public function down(): void
    {
        Schema::table('shop_orders', function (Blueprint $table) {
            $table->dropIndex('shop_orders_status_estimated_completion_index');
            $table->dropColumn(['in_progress_at', 'overdue_notified_at']);
        });
    }
};
