<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateShopRequest;
use Inertia\Inertia;
use App\Models\Shop;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $query = Shop::with(['owner'])->latest();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('shop_name', 'like', "%{$request->search}%")
                    ->orWhereHas(
                        'owner',
                        fn($o) => $o
                            ->where('name',  'like', "%{$request->search}%")
                            ->orWhere('email', 'like', "%{$request->search}%")
                    )
                    ->orWhere('phone', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('plan')) {
            if ($request->plan === 'none') {
                $query->whereDoesntHave('owner', function ($q) {
                    $q->whereHas('orders', fn($o) => $o->where('status', 'paid'));
                });
            } else {
                $query->whereHas(
                    'owner.orders',
                    fn($o) => $o
                        ->where('status', 'paid')
                        ->where('subscription_plan', $request->plan)
                        ->latest()
                        ->limit(1)
                );
            }
        }

        $paginated = $query->paginate(15)->withQueryString();

        $shops = $paginated->through(function ($shop) {
            $latestOrder = \App\Models\Order::where('user_id', $shop->owner_id)
                ->where('status', 'paid')
                ->latest()
                ->first();

            return [
                'id'               => $shop->id,
                'shop_name'        => $shop->shop_name,
                'branch_name'      => $shop->branch_name,
                'phone'            => $shop->phone,
                'municipality'     => $shop->municipality,
                'barangay'         => $shop->barangay,
                'status'           => $shop->status,
                'disable_reason'   => $shop->disable_reason,
                'created_at'       => $shop->created_at,
                'owner'            => $shop->owner ? [
                    'id'    => $shop->owner->id,
                    'name'  => $shop->owner->name,
                    'email' => $shop->owner->email,
                    'phone' => $shop->owner->phone ?? null,
                ] : null,
                'subscription_plan' => $latestOrder?->subscription_plan ?? null,
                'expires_at'        => $latestOrder?->expires_at ?? null,
                'is_expired'        => $latestOrder?->expires_at
                    ? \Carbon\Carbon::parse($latestOrder->expires_at)->isPast()
                    : true,
                'is_expiring_soon'  => $latestOrder?->expires_at
                    ? \Carbon\Carbon::parse($latestOrder->expires_at)->between(now(), now()->addDays(7))
                    : false,
            ];
        });

        $stats = [
            'today'  => Shop::whereDate('created_at', \Carbon\Carbon::today())->count(),
            'total'  => Shop::count(),
            'active' => Shop::where('status', 'active')->count(),
        ];

        return Inertia::render('admin/shop/Index', [
            'shops'   => $shops,
            'stats'   => $stats,
            'filters' => $request->only(['search', 'status', 'plan']),
        ]);
    }

    public function show(Shop $shop)
    {
        $shop->load('owner');

        $latestOrder = Order::where('user_id', $shop->owner_id)
            ->where('status', 'paid')
            ->latest()
            ->first();

        return Inertia::render('admin/shop/Show', [
            'shop' => array_merge($shop->toArray(), [
                'latest_order' => $latestOrder ? [
                    'subscription_plan' => $latestOrder->subscription_plan,
                    'expires_at'        => $latestOrder->expires_at,
                    'total_price'       => $latestOrder->total_price,
                    'status'            => $latestOrder->status,
                    'created_at'        => $latestOrder->created_at,
                ] : null,
            ]),
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
        $shop->delete();

        return redirect()->route('shop.index')
            ->with('success', 'Shop archived successfully.');
    }

    public function disable(Request $request, Shop $shop)
    {
        $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $shop->update([
            'status'         => 'disabled',
            'disable_reason' => $request->reason,
        ]);

        return redirect()->back()
            ->with('toast', ['type' => 'warning', 'message' => "{$shop->shop_name} has been disabled."]);
    }

    public function enable(Shop $shop)
    {
        $shop->update([
            'status'         => 'active',
            'disable_reason' => null,
        ]);

        return redirect()->back()
            ->with('toast', ['type' => 'success', 'message' => "{$shop->shop_name} has been re-enabled."]);
    }
}
