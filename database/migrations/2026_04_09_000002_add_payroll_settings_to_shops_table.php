<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->boolean('deduct_sss')->default(true)->after('disable_reason');
            $table->boolean('deduct_philhealth')->default(true)->after('deduct_sss');
            $table->boolean('deduct_pagibig')->default(true)->after('deduct_philhealth');
            $table->boolean('deduct_withholding_tax')->default(true)->after('deduct_pagibig');
        });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn(['deduct_sss', 'deduct_philhealth', 'deduct_pagibig', 'deduct_withholding_tax']);
        });
    }
};
