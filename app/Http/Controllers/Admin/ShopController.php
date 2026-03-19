<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateShopRequest;
use Inertia\Inertia;
use App\Models\Shop;
use Carbon\Carbon;

class ShopController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        $shops = Shop::with('owner')->get();

        $stats = [
            'today' => Shop::whereDate('created_at', $today)->count(),
            'total' => Shop::count(),
            'active' => Shop::where('status', 'active')->count(),
        ];

        return Inertia::render('admin/shop/Index', [
            'shops' => $shops,
            'stats' => $stats,
        ]);
    }

    public function show(Shop $shop)
    {
        $shop->load('owner');

        return Inertia::render('admin/shop/Show', [
            'shop' => $shop,
        ]);
    }

    public function edit(Shop $shop)
    {
        $shop->load('owner');

        return Inertia::render('admin/shop/Edit', [
            'shop' => $shop,
        ]);
    }

    public function update(UpdateShopRequest $request, Shop $shop)
    {
        $shop->update($request->validated());

        return redirect()->route('shop.show', $shop->id)
            ->with('toast', ['type' => 'success', 'message' => 'Shop updated successfully.']);
    }

    public function destroy(Shop $shop)
    {
        // Soft delete or archive logic here.
        $shop->delete();

        return redirect()->route('shop.index')
            ->with('success', 'Shop archived successfully.');
    }
}
