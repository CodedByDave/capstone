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
        Schema::table('employee_activity_logs', function (Blueprint $table) {
            // Drop existing foreign keys first
            $table->dropForeign(['employee_id']);
            $table->dropForeign(['performed_by']);

            // Re-add as nullable with nullOnDelete
            $table->foreignId('employee_id')->nullable()->change();
            $table->foreignId('performed_by')->nullable()->change();

            $table->foreign('employee_id')->references('id')->on('employees')->nullOnDelete();
            $table->foreign('performed_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('employee_activity_logs', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->dropForeign(['performed_by']);

            $table->foreignId('employee_id')->nullable(false)->change();
            $table->foreignId('performed_by')->nullable(false)->change();

            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->foreign('performed_by')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
