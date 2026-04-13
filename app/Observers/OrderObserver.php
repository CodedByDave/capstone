<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\Shop;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    public function updated(Order $order): void
    {
        // When order becomes paid — set expiry based on subscription plan
        if ($order->wasChanged('status') && $order->status === 'paid' && is_null($order->expires_at)) {

            $expiresAt = $order->subscription_plan === 'annually'
                ? now()->addDays(365)
                : now()->addDays(30);

            $order->updateQuietly([
                'expires_at' => $expiresAt,
            ]);

            Shop::where('owner_id', $order->user_id)
                ->update(['status' => 'active']);

            Log::info('Shop activated, subscription expires at', [
                'order_id'          => $order->id,
                'subscription_plan' => $order->subscription_plan,
                'expires_at'        => $expiresAt,
            ]);
        }

        // When order becomes expired — deactivate the shop only if no other active paid/approved orders
        if ($order->wasChanged('status') && $order->status === 'expired') {
            $hasActivePaidOrder = Order::where('user_id', $order->user_id)
                ->whereIn('status', ['paid', 'approved'])
                ->where('expires_at', '>', now())
                ->exists();

            if (! $hasActivePaidOrder) {
                Shop::where('owner_id', $order->user_id)
                    ->update(['status' => 'inactive']);

                Log::info('Shop deactivated — no active paid orders remaining', [
                    'order_id' => $order->id,
                    'user_id'  => $order->user_id,
                ]);
            }
        }
    }
}
