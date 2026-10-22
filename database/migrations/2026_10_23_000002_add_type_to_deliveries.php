<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            // 'delivery' brings laundry back to the customer; 'pickup' collects it from them.
            $table->string('type', 10)->default('delivery')->after('shop_order_id');
            $table->index(['shop_order_id', 'type']);
        });

        // Open pickup orders placed before this existed still need their rider run.
        $orders = DB::table('shop_orders')
            ->join('shops', 'shops.id', '=', 'shop_orders.shop_id')
            ->where('shops.offers_delivery', true)
            ->where('shop_orders.pickup_type', 'pickup')
            ->whereIn('shop_orders.status', ['pending', 'in_progress'])
            ->whereNull('shop_orders.deleted_at')
            ->select('shop_orders.id', 'shop_orders.shop_id', 'shop_orders.customer_name', 'shop_orders.customer_phone', 'shop_orders.customer_address', 'shop_orders.user_id')
            ->get();

        foreach ($orders as $order) {
            DB::table('deliveries')->insert([
                'public_id' => (string) \Illuminate\Support\Str::ulid(),
                'driver_token' => \Illuminate\Support\Str::random(48),
                'shop_id' => $order->shop_id,
                'shop_order_id' => $order->id,
                'type' => 'pickup',
                'customer_name' => $order->customer_name,
                'customer_phone' => $order->customer_phone,
                'customer_email' => $order->user_id
                    ? DB::table('users')->where('id', $order->user_id)->value('email')
                    : null,
                'delivery_address' => $order->customer_address,
                'status' => 'pending',
                'notes' => 'Created for an order placed before pickup runs were tracked.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('deliveries')->where('type', 'pickup')->delete();

        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropIndex(['shop_order_id', 'type']);
            $table->dropColumn('type');
        });
    }
};
