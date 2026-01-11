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
        Schema::create('shop_user', function (Blueprint $table) {
            $table->id();

            //Foreign keys
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->foreignId(column: 'user_id')->constrained('users')->cascadeOnDelete();

            //Roles
            $table->enum('role', ['owner', 'staff'])->default('staff');

            // Ensure a user can't have multiple roles in the same shop
            $table->unique(['shop_id', 'user_id']);

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_user');
    }
};
