<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shop_orders', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->nullable()
                ->after('shop_id')
                ->constrained('users')
                ->nullOnDelete();

            $table->enum('order_source', ['shop', 'online'])->default('shop')->after('user_id');

            $table->text('special_instructions')->nullable()->after('customer_address');
        });
    }

    public function down(): void
    {
        Schema::table('shop_orders', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'order_source', 'special_instructions']);
        });
    }
};
