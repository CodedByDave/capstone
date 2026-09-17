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
        Schema::table('orders', function (Blueprint $table) {
            $table->ulid('public_id')->nullable()->unique()->after('id');
        });

        DB::table('orders')
            ->whereNull('public_id')
            ->orderBy('id')
            ->eachById(function ($order) {
                DB::table('orders')
                    ->where('id', $order->id)
                    ->update(['public_id' => (string) Str::ulid()]);
            });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['public_id']);
            $table->dropColumn('public_id');
        });
    }
};
