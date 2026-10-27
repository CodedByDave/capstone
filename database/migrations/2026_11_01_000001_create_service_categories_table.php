<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->string('key');
            $table->string('name');
            $table->string('description', 500)->nullable();
            $table->timestamps();
            $table->unique(['shop_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_categories');
    }
};
