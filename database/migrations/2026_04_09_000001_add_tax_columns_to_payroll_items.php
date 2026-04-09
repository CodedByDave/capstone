<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payroll_items', function (Blueprint $table) {
            $table->decimal('sss_contribution', 10, 2)->default(0)->after('deductions');
            $table->decimal('philhealth_contribution', 10, 2)->default(0)->after('sss_contribution');
            $table->decimal('pagibig_contribution', 10, 2)->default(0)->after('philhealth_contribution');
            $table->decimal('withholding_tax', 10, 2)->default(0)->after('pagibig_contribution');
        });
    }

    public function down(): void
    {
        Schema::table('payroll_items', function (Blueprint $table) {
            $table->dropColumn(['sss_contribution', 'philhealth_contribution', 'pagibig_contribution', 'withholding_tax']);
        });
    }
};
