<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services_and_pricing', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();

            $table->string('service_name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);

            $table->enum('pricing_model', ['per_kg', 'per_bundle'])->default('per_kg');

            $table->decimal('price_per_kg', 10, 2)->nullable();

            $table->decimal('bundle_weight_kg', 8, 2)->nullable();
            $table->decimal('bundle_price', 10, 2)->nullable();

            $table->unsignedInteger('estimated_hours')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services_and_pricing');
    }
};
