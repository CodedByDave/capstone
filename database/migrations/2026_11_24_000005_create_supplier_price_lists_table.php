<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_price_lists', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->string('photo_path');
            $table->longText('ocr_text')->nullable();
            $table->enum('status', ['pending', 'verified', 'mismatch', 'unreadable'])->default('pending');
            $table->json('match_results')->nullable();
            $table->timestamp('checked_at')->nullable();
            $table->timestamps();

            $table->index(['supplier_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_price_lists');
    }
};
