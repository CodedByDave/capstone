<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\Module;
use Inertia\Inertia;

class ShopOrderController extends Controller
{
    private function getShop(): Shop
    {
        return Shop::where('owner_id', auth()->id())->firstOrFail();
    }

    public function displayModules()
    {
        $shop  = $this->getShop();
        $order = $shop->order()->with('modules')->first();

        return Inertia::render('shop/Dashboard', [
            'modules' => Module::all(),
            'order'   => $order ? [
                'status'  => $order->status,
                'modules' => $order->modules->map(fn($m) => [
                    'name'  => $m->name,
                    'price' => $m->price,
                ]),
            ] : null,
        ]);
    }
}
