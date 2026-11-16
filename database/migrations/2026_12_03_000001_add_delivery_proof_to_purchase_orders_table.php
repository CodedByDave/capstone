<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            // The supplier's photo of the delivery, taken when they hand it over.
            $table->string('supplier_proof_path')->nullable()->after('received_by');
            $table->timestamp('supplier_delivered_at')->nullable()->after('supplier_proof_path');
            // The shop's photo of what arrived, taken when it marks the order received.
            $table->string('received_proof_path')->nullable()->after('supplier_delivered_at');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['supplier_proof_path', 'supplier_delivered_at', 'received_proof_path']);
        });
    }
};
