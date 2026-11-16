<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Customers are told their laundry is done by email (optional on the order); SMS is gone.
        Schema::table('shop_orders', function (Blueprint $table) {
            $table->string('customer_email')->nullable()->after('customer_phone');
            $table->timestamp('ready_email_sent_at')->nullable();
        });

        Schema::dropIfExists('sms_messages');

        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn([
                'sms_gateway_enabled', 'sms_gateway_mode', 'sms_gateway_url',
                'sms_gateway_username', 'sms_gateway_password', 'sms_notify_ready',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->boolean('sms_gateway_enabled')->default(false);
            $table->string('sms_gateway_mode', 10)->default('cloud');
            $table->string('sms_gateway_url')->nullable();
            $table->string('sms_gateway_username')->nullable();
            $table->text('sms_gateway_password')->nullable();
            $table->boolean('sms_notify_ready')->default(true);
        });

        Schema::create('sms_messages', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shop_order_id')->nullable()->constrained('shop_orders')->nullOnDelete();
            $table->string('purpose', 20);
            $table->string('provider', 20)->default('smsgate');
            $table->string('phone', 20);
            $table->text('message');
            $table->string('status', 10);
            $table->string('gateway_message_id')->nullable();
            $table->string('error')->nullable();
            $table->foreignId('sent_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('shop_orders', function (Blueprint $table) {
            $table->dropColumn(['customer_email', 'ready_email_sent_at']);
        });
    }
};
