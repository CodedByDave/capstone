<?php

use App\Support\CustomerIdentity;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * A customer is identified by name alone, so the same name with a different number is one
     * customer. Customer records that were split by number are merged into the oldest one, and its
     * number and email follow the most recent order.
     */
    public function up(): void
    {
        $groups = DB::table('shop_customers')
            ->orderBy('id')
            ->get(['id', 'shop_id', 'name', 'identity_key'])
            ->groupBy(fn ($customer) => $customer->shop_id.'#'.CustomerIdentity::key($customer->name));

        foreach ($groups as $customers) {
            $keep = $customers->first();
            $duplicates = $customers->skip(1)->pluck('id')->all();

            if ($duplicates !== []) {
                DB::table('shop_orders')->whereIn('shop_customer_id', $duplicates)->update(['shop_customer_id' => $keep->id]);
                DB::table('shop_customers')->whereIn('id', $duplicates)->delete();
            }

            $latest = DB::table('shop_orders')
                ->where('shop_customer_id', $keep->id)
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->first(['customer_phone', 'customer_email']);

            $current = DB::table('shop_customers')->where('id', $keep->id)->first(['phone', 'email']);

            DB::table('shop_customers')->where('id', $keep->id)->update([
                'identity_key' => CustomerIdentity::key($keep->name),
                'phone' => filled($latest?->customer_phone) ? $latest->customer_phone : $current->phone,
                'email' => filled($current->email) ? $current->email : ($latest?->customer_email ?: null),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Merged customers cannot be split apart again.
    }
};
