<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shop_orders', function (Blueprint $table) {
            // The photo the shop took when weighing, and the customer's decision on that weight.
            $table->string('weight_proof_path')->nullable();
            $table->string('weight_approval_status', 20)->nullable()->index();
            $table->timestamp('weight_proof_uploaded_at')->nullable();
            $table->timestamp('weight_decided_at')->nullable();
        });

        // Online orders weighed before this feature need no approval.
        DB::table('shop_orders')
            ->where('order_source', 'online')
            ->whereNotNull('actual_weight_kg')
            ->update(['weight_approval_status' => 'approved']);
    }

    public function down(): void
    {
        Schema::table('shop_orders', function (Blueprint $table) {
            $table->dropIndex(['weight_approval_status']);
            $table->dropColumn(['weight_proof_path', 'weight_approval_status', 'weight_proof_uploaded_at', 'weight_decided_at']);
        });
    }
};
