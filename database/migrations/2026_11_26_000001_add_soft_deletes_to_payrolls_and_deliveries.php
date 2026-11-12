<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Payrolls and deliveries were removed for good when a shop deleted them. They are now archived
 * instead, so the shop can still look them up and restore them.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('deliveries', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
