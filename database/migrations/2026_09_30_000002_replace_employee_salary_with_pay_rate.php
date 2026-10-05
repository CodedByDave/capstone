<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->decimal('pay_rate', 10, 2)->nullable()->after('employment_type');
            $table->string('pay_basis', 30)->default('monthly')->after('pay_rate');
        });

        Schema::table('employee_archives', function (Blueprint $table) {
            $table->decimal('pay_rate', 10, 2)->nullable()->after('employment_type');
            $table->string('pay_basis', 30)->default('monthly')->after('pay_rate');
        });

        DB::table('employees')->update(['pay_rate' => DB::raw('salary')]);
        DB::table('employee_archives')->update(['pay_rate' => DB::raw('salary')]);

        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('salary');
        });

        Schema::table('employee_archives', function (Blueprint $table) {
            $table->dropColumn('salary');
        });

        Schema::table('payroll_items', function (Blueprint $table) {
            $table->decimal('pay_rate', 10, 2)->default(0)->after('basic_salary');
            $table->string('pay_basis', 30)->default('monthly')->after('pay_rate');
        });

        DB::table('payroll_items')->update(['pay_rate' => DB::raw('basic_salary')]);
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->decimal('salary', 10, 2)->nullable()->after('hire_date');
        });

        Schema::table('employee_archives', function (Blueprint $table) {
            $table->decimal('salary', 10, 2)->nullable()->after('hire_date');
        });

        DB::table('employees')->update(['salary' => DB::raw('pay_rate')]);
        DB::table('employee_archives')->update(['salary' => DB::raw('pay_rate')]);

        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['pay_rate', 'pay_basis']);
        });

        Schema::table('employee_archives', function (Blueprint $table) {
            $table->dropColumn(['pay_rate', 'pay_basis']);
        });

        Schema::table('payroll_items', function (Blueprint $table) {
            $table->dropColumn(['pay_rate', 'pay_basis']);
        });
    }
};
