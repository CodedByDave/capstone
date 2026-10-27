<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_supplies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained('services_and_pricing')->cascadeOnDelete();
            $table->foreignId('inventory_id')->constrained('inventory')->restrictOnDelete();
            // Per kg for per-kg services, per bundle for bundle services.
            $table->decimal('quantity', 8, 2);
            $table->string('unit', 50);
            $table->timestamps();
            $table->unique(['service_id', 'inventory_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_supplies');
    }
};
