<?php

namespace App\Http\Middleware;

use App\Models\Order;
use App\Models\Shop;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckShopSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            $shop = Shop::where('owner_id', $user->id)->first();

            // Only run check if this user owns a shop
            if ($shop) {
                // Step 1: Mark any overdue paid orders as expired
                Order::where('user_id', $user->id)
                    ->where('status', 'paid')
                    ->where('expires_at', '<=', now())
                    ->each(function (Order $order) {
                        // updateQuietly skips the observer to avoid double-processing
                        $order->updateQuietly(['status' => 'expired']);

                        Log::info('Order expired via middleware', [
                            'order_id'   => $order->id,
                            'user_id'    => $order->user_id,
                            'expired_at' => $order->expires_at,
                        ]);
                    });

                // Step 2: Check if any active paid order still exists
                $hasActivePaidOrder = Order::where('user_id', $user->id)
                    ->where('status', 'paid')
                    ->where('expires_at', '>', now())
                    ->exists();

                // Step 3: Sync shop status accordingly
                if (! $hasActivePaidOrder && $shop->status === 'active') {
                    $shop->update(['status' => 'inactive']);

                    Log::info('Shop deactivated — no active paid orders remaining', [
                        'shop_id' => $shop->id,
                        'user_id' => $user->id,
                    ]);
                }

                if ($hasActivePaidOrder && $shop->status === 'inactive') {
                    $shop->update(['status' => 'active']);

                    Log::info('Shop re-activated — active paid order found', [
                        'shop_id' => $shop->id,
                        'user_id' => $user->id,
                    ]);
                }
            }
        }

        return $next($request);
    }
}
