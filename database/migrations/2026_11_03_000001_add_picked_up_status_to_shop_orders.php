<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * An order now ends at "picked up" once the customer has collected the finished laundry. The time
     * is kept so the shop can see when the order left the shop.
     */
    public function up(): void
    {
        Schema::table('shop_orders', function (Blueprint $table) {
            $table->enum('status', ['pending', 'in_progress', 'completed', 'picked_up', 'cancelled'])
                ->default('pending')
                ->change();
            $table->timestamp('picked_up_at')->nullable()->after('completed_at');
        });
    }

    public function down(): void
    {
        // An order that was picked up goes back to completed so the narrower list still holds it.
        DB::table('shop_orders')->where('status', 'picked_up')->update(['status' => 'completed']);

        Schema::table('shop_orders', function (Blueprint $table) {
            $table->dropColumn('picked_up_at');
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])
                ->default('pending')
                ->change();
        });
    }
};
