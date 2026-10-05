<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('employment_type', 30)
                ->nullable()
                ->after('position');
            $table->index(['shop_id', 'employment_type'], 'employees_shop_employment_type_idx');
        });

        Schema::table('employee_archives', function (Blueprint $table) {
            $table->string('employment_type', 30)
                ->nullable()
                ->after('position');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropIndex('employees_shop_employment_type_idx');
            $table->dropColumn('employment_type');
        });

        Schema::table('employee_archives', function (Blueprint $table) {
            $table->dropColumn('employment_type');
        });
    }
};
