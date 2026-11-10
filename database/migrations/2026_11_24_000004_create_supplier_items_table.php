<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_items', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->string('name');
            $table->string('unit', 50)->nullable();
            $table->decimal('stock_quantity', 12, 2)->default(0);
            $table->decimal('unit_price', 10, 2);
            $table->timestamps();

            $table->index(['supplier_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_items');
    }
};
