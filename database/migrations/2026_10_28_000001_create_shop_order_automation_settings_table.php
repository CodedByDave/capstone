<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_order_automation_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->unique()->constrained()->cascadeOnDelete();
            // Every rule is opt-in: a shop decides which status changes the system may make.
            $table->boolean('start_on_weigh_in')->default(false);
            $table->boolean('start_on_payment')->default(false);
            $table->boolean('auto_complete')->default(false);
            $table->unsignedSmallInteger('auto_complete_grace_minutes')->default(120);
            $table->boolean('remind_overdue')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_order_automation_settings');
    }
};
