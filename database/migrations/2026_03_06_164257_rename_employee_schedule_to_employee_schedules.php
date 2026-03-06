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
        Schema::rename('employee_schedule', 'employee_schedules');
    }

    public function down(): void
    {
        Schema::rename('employee_schedules', 'employee_schedule');
    }
};
