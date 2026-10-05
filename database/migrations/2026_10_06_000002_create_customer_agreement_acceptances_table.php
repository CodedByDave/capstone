<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_agreement_acceptances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_order_id')->unique()->constrained('shop_orders')->cascadeOnDelete();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->foreignId('customer_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('accepted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('acceptance_method', 30);
            $table->string('customer_name');
            $table->string('agreement_version', 30);
            $table->string('agreement_title');
            $table->longText('agreement_content');
            $table->char('content_hash', 64);
            $table->timestamp('accepted_at');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_agreement_acceptances');
    }
};
