<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Orders completed through the edit form never got a completion time. Use the last time the
        // order changed, the best record there is, so rating windows and prompts can rely on it.
        DB::table('shop_orders')
            ->where('status', 'completed')
            ->whereNull('completed_at')
            ->update(['completed_at' => DB::raw('updated_at')]);

        Schema::table('shop_orders', function (Blueprint $table) {
            $table->index(['shop_id', 'status', 'completed_at'], 'shop_orders_shop_status_completed_index');
        });
    }

    public function down(): void
    {
        // The stamped completion times cannot be told apart from real ones, so they stay.
        Schema::table('shop_orders', function (Blueprint $table) {
            $table->dropIndex('shop_orders_shop_status_completed_index');
        });
    }
};
