<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services_and_pricing', function (Blueprint $table) {
            $table->string('category', 50)
                ->default('full_service')
                ->after('service_name')
                ->index();
        });
    }

    public function down(): void
    {
        Schema::table('services_and_pricing', function (Blueprint $table) {
            $table->dropIndex(['category']);
            $table->dropColumn('category');
        });
    }
};
