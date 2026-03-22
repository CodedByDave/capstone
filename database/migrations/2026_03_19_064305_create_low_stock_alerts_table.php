<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('low_stock_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_id')->constrained('inventory')->cascadeOnDelete();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();

            $table->integer('quantity_at_alert');          // stock level when alert was triggered
            $table->integer('min_stock_at_alert');         // min_stock threshold at that time

            $table->enum('status', [
                'unread',       // newly triggered, not seen
                'read',         // admin has seen it
                'resolved',     // stock has been restocked
                'dismissed',    // manually dismissed without restocking
            ])->default('unread');

            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('low_stock_alerts');
    }
};
