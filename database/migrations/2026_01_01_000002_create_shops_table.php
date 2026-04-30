<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('shop_name');
            $table->string('branch_name')->nullable();
            $table->string('phone');
            $table->string('block_street')->nullable();
            $table->string('municipality');
            $table->string('barangay');
            $table->string('postal_code');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('cover_photo')->nullable();
            $table->string('gcash_qr')->nullable();
            $table->string('maya_qr')->nullable();
            $table->string('status')->default('pending');
            $table->text('disable_reason')->nullable();
            $table->boolean('deduct_sss')->default(true);
            $table->boolean('deduct_philhealth')->default(true);
            $table->boolean('deduct_pagibig')->default(true);
            $table->boolean('deduct_withholding_tax')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        // Resolve circular dep: users.shop_id FK now that shops exists
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('shop_id')->references('id')->on('shops')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['shop_id']);
        });
        Schema::dropIfExists('shops');
    }
};
