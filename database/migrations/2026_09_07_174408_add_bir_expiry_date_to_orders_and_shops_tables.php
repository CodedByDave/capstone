<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->date('bir_expiry_date')->nullable()->before('mayors_expiry_date');
        });

        Schema::table('shops', function (Blueprint $table) {
            $table->date('bir_expiry_date')->nullable()->before('mayors_expiry_date');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('bir_expiry_date');
        });

        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn('bir_expiry_date');
        });
    }
};
