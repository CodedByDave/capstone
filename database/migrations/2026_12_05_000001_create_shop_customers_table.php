<?php

use App\Support\CustomerIdentity;
use App\Support\PhilippinePhone;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // One record per customer of a shop, so a returning customer is one name with many orders
        // instead of the same name copied onto every order.
        Schema::create('shop_customers', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            // The app account of a customer who ordered online, when known.
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            // The same name is the same customer (see CustomerIdentity).
            $table->string('identity_key', 300);
            $table->timestamps();

            $table->unique(['shop_id', 'identity_key']);
            $table->index(['shop_id', 'name']);
            $table->index(['shop_id', 'phone']);
        });

        Schema::table('shop_orders', function (Blueprint $table) {
            $table->foreignId('shop_customer_id')->nullable()->after('user_id')
                ->constrained('shop_customers')->nullOnDelete();
        });

        $this->linkExistingOrders();
    }

    public function down(): void
    {
        Schema::table('shop_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('shop_customer_id');
        });

        Schema::dropIfExists('shop_customers');
    }

    /** Gives every existing order its customer, matching the same number and name within a shop. */
    private function linkExistingOrders(): void
    {
        $known = [];

        DB::table('shop_orders')
            ->orderBy('id')
            ->select(['id', 'shop_id', 'user_id', 'customer_name', 'customer_phone', 'customer_email'])
            ->chunkById(500, function ($orders) use (&$known): void {
                foreach ($orders as $order) {
                    if (CustomerIdentity::isAnonymous($order->customer_name, $order->customer_phone)) {
                        continue;
                    }

                    $key = CustomerIdentity::key($order->customer_name);
                    $cacheKey = $order->shop_id.'#'.$key;

                    $known[$cacheKey] ??= DB::table('shop_customers')->insertGetId([
                        'public_id' => (string) Str::ulid(),
                        'shop_id' => $order->shop_id,
                        'user_id' => $order->user_id,
                        'name' => trim((string) preg_replace('/\s+/u', ' ', $order->customer_name)),
                        'phone' => PhilippinePhone::format($order->customer_phone),
                        'email' => $order->customer_email,
                        'identity_key' => $key,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    DB::table('shop_orders')->where('id', $order->id)->update(['shop_customer_id' => $known[$cacheKey]]);
                }
            });
    }
};
