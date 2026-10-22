<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    private const METHODS = ['cash', 'gcash', 'maya', 'card', 'grabpay', 'online'];

    public function up(): void
    {
        Schema::create('shop_order_payments', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->foreignId('shop_order_id')->constrained('shop_orders')->cascadeOnDelete();
            // Kept on the row so a shop's payments are scoped without joining the order.
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('method', 20);
            $table->string('reference', 100)->nullable();
            $table->string('note', 255)->nullable();
            // pending (online, waiting for PayMongo) | confirmed | voided | cancelled (abandoned online)
            $table->string('status', 12)->default('confirmed');
            // shop (recorded by staff) | online (paid through PayMongo)
            $table->string('source', 10)->default('shop');
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('paid_at')->nullable();
            $table->string('paymongo_session_id')->nullable()->index();
            $table->string('paymongo_payment_id')->nullable();
            $table->timestamp('voided_at')->nullable();
            $table->foreignId('voided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('void_reason', 255)->nullable();
            $table->timestamps();

            $table->index(['shop_order_id', 'status']);
            $table->index(['shop_id', 'status', 'paid_at']);
            $table->index(['status', 'created_at']);
        });

        $this->backfillExistingPayments();
    }

    public function down(): void
    {
        // Orders keep their own paid totals, so dropping the history loses no balances.
        Schema::dropIfExists('shop_order_payments');
    }

    /**
     * Every order that already shows money received gets one confirmed payment for that amount,
     * so old orders have a history and the totals still add up.
     */
    private function backfillExistingPayments(): void
    {
        DB::table('shop_orders')
            ->where(fn ($q) => $q->where('amount_paid', '>', 0)
                ->orWhere(fn ($q) => $q->where('payment_status', 'paid')->where('total_amount', '>', 0)))
            ->orderBy('id')
            ->select('id', 'shop_id', 'amount_paid', 'total_amount', 'payment_status', 'payment_method', 'paid_at', 'updated_at')
            ->chunkById(200, function ($orders): void {
                $rows = [];

                foreach ($orders as $order) {
                    $method = in_array($order->payment_method, self::METHODS, true) ? $order->payment_method : 'cash';

                    $rows[] = [
                        'public_id' => (string) Str::ulid(),
                        'shop_order_id' => $order->id,
                        'shop_id' => $order->shop_id,
                        'amount' => $order->amount_paid > 0 ? $order->amount_paid : $order->total_amount,
                        'method' => $method,
                        'note' => 'Recorded before payment history was kept.',
                        'status' => 'confirmed',
                        'source' => 'shop',
                        'paid_at' => $order->paid_at ?? $order->updated_at ?? now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                DB::table('shop_order_payments')->insert($rows);
            });
    }
};
