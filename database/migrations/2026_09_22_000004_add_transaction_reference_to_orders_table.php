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
            $table->string('transaction_reference', 30)->nullable()->unique()->after('public_id');
        });

        DB::table('orders')
            ->whereNull('transaction_reference')
            ->orderBy('id')
            ->eachById(function ($order) {
                DB::table('orders')
                    ->where('id', $order->id)
                    ->update([
                        'transaction_reference' => 'TXN-'.Str::upper((string) Str::ulid()),
                    ]);
            });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['transaction_reference']);
            $table->dropColumn('transaction_reference');
        });
    }
};
