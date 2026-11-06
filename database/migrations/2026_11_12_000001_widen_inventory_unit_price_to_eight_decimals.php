<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** ₱160 per 3,000 ml is ₱0.05333… per ml; four decimals turned it back into ₱159.90 per bottle. */
    public function up(): void
    {
        Schema::table('inventory', function (Blueprint $table) {
            $table->decimal('unit_price', 16, 8)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('inventory', function (Blueprint $table) {
            $table->decimal('unit_price', 12, 4)->nullable()->change();
        });
    }
};
