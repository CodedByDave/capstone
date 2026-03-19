<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\Module;
use App\Models\Order;
use Inertia\Inertia;

class ShopOrderController extends Controller
{
    private function getShop(): Shop
    {
        return Shop::where('owner_id', auth()->id())->firstOrFail();
    }

    public function displayModules()
    {
        $shop = $this->getShop();

        // Always get the latest active paid order — not just any first order
        $order = Order::where('user_id', auth()->id())
            ->where('status', 'paid')
            ->where('expires_at', '>', now())
            ->latest()
            ->with('modules')
            ->first();

        return Inertia::render('shop/Dashboard', [
            'modules' => Module::all(),
            'order'   => $order ? [
                'status'            => $order->status,
                'shop_name'         => $order->shop_name,
                'subscription_plan' => $order->subscription_plan,
                'expires_at'        => $order->expires_at,
                'total_price'       => $order->total_price,
                'modules'           => $order->modules->map(fn($m) => [
                    'name'  => $m->name,
                    'price' => $m->price,
                ]),
            ] : null,
        ]);
    }
}
