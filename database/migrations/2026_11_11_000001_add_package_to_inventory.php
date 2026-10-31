<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory', function (Blueprint $table) {
            // How the item is bought (e.g. "bottle") and how much of its unit one package holds (e.g. 5000 ml).
            $table->string('package_unit', 50)->nullable()->after('unit');
            $table->unsignedInteger('package_size')->nullable()->after('package_unit');
        });
    }

    public function down(): void
    {
        Schema::table('inventory', function (Blueprint $table) {
            $table->dropColumn(['package_unit', 'package_size']);
        });
    }
};
