<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            // What produced the expense, so automatic ones can be found and kept in step.
            $table->string('source', 30)->nullable()->after('shop_order_id')->index();
            $table->foreignId('payroll_id')->nullable()->after('shop_order_id')
                ->constrained('payrolls')->nullOnDelete();
        });

        // Every order-linked expense so far is the supplies cost of that order.
        DB::table('expenses')->whereNotNull('shop_order_id')->update(['source' => 'order_supplies']);
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('payroll_id');
            $table->dropColumn('source');
        });
    }
};
