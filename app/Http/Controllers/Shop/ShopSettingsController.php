<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ShopSettingsController extends Controller
{
    private function getShop(): Shop
    {
        return Shop::where('owner_id', auth()->id())->firstOrFail();
    }

    public function index()
    {
        $shop = $this->getShop();

        return Inertia::render('shop/Settings', [
            'shop' => [
                'shop_name'    => $shop->shop_name,
                'phone'        => $shop->phone,
                'block_street' => $shop->block_street,
                'municipality' => $shop->municipality,
                'barangay'     => $shop->barangay,
                'postal_code'  => $shop->postal_code,
                'latitude'     => $shop->latitude,
                'longitude'    => $shop->longitude,
            ],
        ]);
    }

    public function updateGeo(Request $request)
    {
        $data = $request->validate([
            'latitude'  => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $shop = $this->getShop();
        $shop->update([
            'latitude'  => $data['latitude']  !== null ? (float) $data['latitude']  : null,
            'longitude' => $data['longitude'] !== null ? (float) $data['longitude'] : null,
        ]);

        return back()->with('toast', [
            'type'    => 'success',
            'message' => 'Shop location updated successfully.',
        ]);
    }

    public function clearGeo()
    {
        $shop = $this->getShop();
        $shop->update(['latitude' => null, 'longitude' => null]);

        return back()->with('toast', [
            'type'    => 'success',
            'message' => 'Shop location cleared.',
        ]);
    }
}
