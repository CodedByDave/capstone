<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_ratings', function (Blueprint $table) {
            $table->id();
            // One rating per order; shop_id is kept so a shop's ratings never need a join to scope.
            $table->foreignId('shop_order_id')->unique()->constrained('shop_orders')->cascadeOnDelete();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->string('comment', 500)->nullable();
            $table->timestamps();

            $table->index(['shop_id', 'created_at']);
            $table->index(['shop_id', 'rating']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_ratings');
    }
};
