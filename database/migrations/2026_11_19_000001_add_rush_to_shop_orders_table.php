<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shop_orders', function (Blueprint $table) {
            // A rush order goes to the front of the shop's queue and carries the shop's rush fee.
            $table->boolean('is_rush')->default(false)->after('status');
            $table->decimal('rush_fee', 10, 2)->default(0)->after('is_rush');
            $table->timestamp('rush_marked_at')->nullable()->after('rush_fee');

            $table->index(['shop_id', 'status', 'is_rush', 'created_at'], 'shop_orders_queue_index');
        });
    }

    public function down(): void
    {
        Schema::table('shop_orders', function (Blueprint $table) {
            $table->dropIndex('shop_orders_queue_index');
            $table->dropColumn(['is_rush', 'rush_fee', 'rush_marked_at']);
        });
    }
};
