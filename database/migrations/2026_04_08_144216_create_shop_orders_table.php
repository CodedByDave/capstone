<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_orders', function (Blueprint $table) {
            // Primary key
            $table->id();

            // Shop reference
            $table->foreignId('shop_id')
                ->constrained('shops')
                ->cascadeOnDelete();

            // Customer info
            $table->string('customer_name');
            $table->string('customer_phone', 20)->nullable();
            $table->text('customer_address')->nullable();

            // Order reference number (e.g. ORD-2024-00001)
            $table->string('order_number', 30)->unique();

            // Laundry details
            $table->foreignId('service_id')
                ->constrained('services_and_pricing')
                ->restrictOnDelete();

            $table->decimal('estimated_weight_kg', 6, 2);
            $table->decimal('actual_weight_kg', 6, 2);

            // Pickup / delivery
            $table->enum('pickup_type', [
                'walk_in',
                'pickup',
            ])->default('walk_in');

            // Pricing
            $table->decimal('price_per_kg', 8, 2)->nullable()->comment('Snapshotted from services_and_pricing at time of order');
            $table->decimal('additional_charges', 8, 2)->default(0.00);
            $table->decimal('discount_amount', 8, 2)->default(0.00);
            $table->decimal('total_amount', 10, 2);

            // Payment
            $table->enum('payment_method', [
                'cash',
                'gcash',
                'maya',
            ])->default('cash');

            $table->enum('payment_status', [
                'unpaid',
                'partial',
                'paid',
            ])->default('unpaid');

            $table->decimal('amount_paid', 10, 2)->default(0.00);
            $table->timestamp('paid_at')->nullable();

            // Order status
            $table->enum('status', [
                'pending',
                'in_progress',
                'completed',
            ])->default('pending');

            // Dates
            $table->timestamp('estimated_completion_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_orders');
    }
};
