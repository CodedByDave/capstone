<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // An order a shop places with one of its own suppliers. The supplier confirms or declines it
        // in the supplier portal; the shop marks it received, which adds the stock.
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained()->restrictOnDelete();
            $table->string('po_number', 30)->unique();
            $table->string('status', 15)->default('draft');
            $table->decimal('total', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->date('expected_date')->nullable();
            $table->string('supplier_note', 500)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('declined_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['shop_id', 'status']);
            $table->index(['supplier_id', 'status']);
        });

        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_item_id')->nullable()->constrained()->nullOnDelete();
            // The shop's inventory item that receives the stock; none means the stock is not tracked.
            $table->foreignId('inventory_id')->nullable()->constrained('inventory')->nullOnDelete();
            // Name, unit and price are copied when ordering, so a later price change keeps the order as placed.
            $table->string('name');
            $table->string('unit', 30)->nullable();
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('line_total', 12, 2);
            // How many inventory stock units one ordered unit adds (a 3,000 ml bottle adds 3,000 ml).
            $table->unsignedInteger('stock_per_unit')->default(1);
            $table->decimal('received_quantity', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
    }
};
