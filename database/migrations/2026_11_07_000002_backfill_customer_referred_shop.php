<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Customers who existed before shop QR sign-up are tied to the shop they ordered from most.
     * Customers without orders stay unlinked until they scan a shop's QR code.
     */
    public function up(): void
    {
        DB::table('users')
            ->where('role', 'user')
            ->whereNull('referred_shop_id')
            ->orderBy('id')
            ->select('id')
            ->chunkById(200, function ($customers) {
                foreach ($customers as $customer) {
                    $shopId = DB::table('shop_orders')
                        ->where('user_id', $customer->id)
                        ->selectRaw('shop_id, count(*) as total, max(created_at) as latest')
                        ->groupBy('shop_id')
                        ->orderByDesc('total')
                        ->orderByDesc('latest')
                        ->value('shop_id');

                    if ($shopId !== null) {
                        DB::table('users')->where('id', $customer->id)->update(['referred_shop_id' => $shopId]);
                    }
                }
            });
    }

    public function down(): void
    {
        // The backfilled links cannot be told apart from links made through a QR code.
    }
};
