<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_complaints', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->foreignId('shop_order_id')->constrained('shop_orders')->cascadeOnDelete();
            // shop_id is kept so a shop's complaints never need a join to scope.
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 30);
            $table->text('description');
            $table->string('status', 20)->default('open');
            $table->text('shop_reply')->nullable();
            $table->foreignId('replied_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('replied_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['shop_id', 'status', 'created_at']);
            $table->index(['shop_order_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_complaints');
    }
};
