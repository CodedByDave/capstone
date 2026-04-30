<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('shop_name');
            $table->string('owner_name');
            $table->string('email');
            $table->string('phone');
            $table->string('block_street')->nullable();
            $table->string('municipality');
            $table->string('barangay');
            $table->string('postal_code')->nullable();
            $table->string('kyc_bir')->nullable();
            $table->string('kyc_dti')->nullable();
            $table->string('kyc_mayors')->nullable();
            $table->string('kyc_sanitary')->nullable();
            $table->string('plan_name')->default('Standard');
            $table->unsignedInteger('billing_months')->default(1);
            $table->boolean('is_upgrade')->default(false);
            $table->boolean('is_trial')->default(false);
            $table->decimal('total_price', 10, 2);
            $table->string('payment_method')->nullable();
            $table->string('status')->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
