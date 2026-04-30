<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('order_source', ['shop', 'online'])->default('shop');
            $table->string('branch_name')->nullable();
            $table->string('customer_name');
            $table->string('customer_phone', 20)->nullable();
            $table->text('customer_address')->nullable();
            $table->text('delivery_address')->nullable();
            $table->text('special_instructions')->nullable();
            $table->string('order_number', 30)->unique();
            $table->foreignId('service_id')->constrained('services_and_pricing')->restrictOnDelete();
            $table->foreignId('promotion_id')->nullable()->constrained('promotions')->nullOnDelete();
            $table->enum('pricing_model', ['per_kg', 'per_bundle'])->default('per_kg');
            $table->decimal('estimated_weight_kg', 6, 2)->nullable();
            $table->decimal('actual_weight_kg', 6, 2)->nullable();
            $table->enum('pickup_type', ['walk_in', 'pickup'])->default('walk_in');
            $table->decimal('price_per_kg', 8, 2)->nullable();
            $table->decimal('bundle_weight_kg', 8, 2)->nullable();
            $table->decimal('bundle_price', 10, 2)->nullable();
            $table->integer('bundle_quantity')->nullable();
            $table->decimal('additional_charges', 8, 2)->default(0.00);
            $table->decimal('discount_amount', 8, 2)->default(0.00);
            $table->decimal('total_amount', 10, 2);
            $table->enum('payment_method', ['cash', 'gcash', 'maya'])->nullable()->default(null);
            $table->enum('payment_status', ['unpaid', 'partial', 'paid'])->default('unpaid');
            $table->decimal('amount_paid', 10, 2)->default(0.00);
            $table->timestamp('paid_at')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->timestamp('estimated_completion_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_orders');
    }
};
