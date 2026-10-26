<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** A shop is a single location, so it no longer carries a branch name. */
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn('branch_name');
        });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->string('branch_name')->nullable();
        });
    }
};
