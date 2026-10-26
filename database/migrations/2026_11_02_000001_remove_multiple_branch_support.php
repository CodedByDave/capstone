<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    /** Each shop is a single location now, so branches and everything that pointed at them go. */
    public function up(): void
    {
        Schema::dropIfExists('branches');

        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('branch_name');
        });

        Schema::table('employee_archives', function (Blueprint $table) {
            $table->dropColumn('branch_name');
        });

        Schema::table('shop_orders', function (Blueprint $table) {
            $table->dropColumn('branch_name');
        });

        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn('location_mode');
        });

        // The permit files uploaded for branches have nothing left to belong to.
        Storage::disk('private')->deleteDirectory('branch-permits');
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->string('location_mode', 20)->nullable();
        });

        Schema::table('shop_orders', function (Blueprint $table) {
            $table->string('branch_name')->nullable();
        });

        Schema::table('employee_archives', function (Blueprint $table) {
            $table->string('branch_name')->nullable();
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->string('branch_name')->nullable();
        });

        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->string('branch_code', 20)->unique();
            $table->string('name');
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('manager_name')->nullable();
            $table->string('address')->nullable();
            $table->string('block_street')->nullable();
            $table->string('municipality')->nullable();
            $table->string('barangay')->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->string('mayors_permit_number')->nullable();
            $table->string('mayors_permit_path')->nullable();
            $table->date('mayors_expiry_date')->nullable();
            $table->string('sanitary_permit_number')->nullable();
            $table->string('sanitary_permit_path')->nullable();
            $table->date('sanitary_expiry_date')->nullable();
            $table->string('permit_status', 20)->default('missing');
            $table->date('opened_at')->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }
};
