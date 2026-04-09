<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['percentage', 'fixed']);
            $table->decimal('value', 8, 2);
            $table->decimal('min_order_amount', 8, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('shop_orders', function (Blueprint $table) {
            $table->foreignId('promotion_id')->nullable()->after('service_id')
                ->constrained('promotions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('shop_orders', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\Promotion::class);
            $table->dropColumn('promotion_id');
        });
        Schema::dropIfExists('promotions');
    }
};
