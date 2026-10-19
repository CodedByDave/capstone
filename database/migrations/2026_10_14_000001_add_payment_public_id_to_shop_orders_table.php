<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shop_orders', function (Blueprint $table) {
            $table->ulid('payment_public_id')->nullable()->unique()->after('id');
        });

        DB::table('shop_orders')
            ->select('id')
            ->whereNull('payment_public_id')
            ->orderBy('id')
            ->chunkById(100, function ($orders): void {
                foreach ($orders as $order) {
                    DB::table('shop_orders')
                        ->where('id', $order->id)
                        ->update(['payment_public_id' => (string) Str::ulid()]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('shop_orders', function (Blueprint $table) {
            $table->dropUnique(['payment_public_id']);
            $table->dropColumn('payment_public_id');
        });
    }
};
