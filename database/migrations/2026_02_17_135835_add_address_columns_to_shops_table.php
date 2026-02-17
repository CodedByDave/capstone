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
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn('street');   // remove old single column

            $table->string('block_street');
            $table->string('municipality');
            $table->string('barangay');
            $table->string('postal_code');
        });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn(['block_street', 'municipality', 'barangay', 'postal_code']);
            $table->string('street');
        });
    }
};
