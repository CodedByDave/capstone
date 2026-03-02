<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shops', function (Blueprint $table) {
            $table->id();

            $table->foreignId('owner_id')
                ->unique()
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('shop_name');
            $table->string('phone');
            $table->string('block_street');
            $table->string('municipality');
            $table->string('barangay');
            $table->string('postal_code');
            $table->string('status')->default('pending');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shops');
    }
};
