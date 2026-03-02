<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_archives', function (Blueprint $table) {
            $table->id();

            $table->foreignId('shop_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // Original employee primary key — kept for reference/joining
            $table->unsignedBigInteger('employee_id_ref');

            $table->string('employee_id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('branch_name')->nullable();
            $table->string('position');
            $table->date('hire_date');
            $table->decimal('salary', 10, 2)->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');

            // When the employee record was originally created
            $table->timestamp('original_created_at')->nullable();

            // When it was archived
            $table->timestamp('archived_at')->useCurrent();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_archives');
    }
};
