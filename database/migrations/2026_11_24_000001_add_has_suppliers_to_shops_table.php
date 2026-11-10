<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->boolean('has_suppliers')->default(false)->after('offers_delivery');
        });

        // Shops that already record suppliers keep their Suppliers menu.
        DB::table('shops')
            ->whereIn('id', DB::table('suppliers')->select('shop_id'))
            ->update(['has_suppliers' => true]);
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn('has_suppliers');
        });
    }
};
