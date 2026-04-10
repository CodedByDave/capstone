<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\ShopOrder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserDashboardController extends Controller
{
    // ─── Dashboard ────────────────────────────────────────────────────────────

    public function index(): Response
    {
        $user = auth()->user();

        $recentOrders = ShopOrder::where('user_id', $user->id)
            ->with(['shop:id,shop_name,municipality,barangay', 'service:id,service_name'])
            ->latest()
            ->take(3)
            ->get()
            ->map(fn($o) => [
                'id'           => $o->id,
                'order_number' => $o->order_number,
                'shop_name'    => $o->shop->shop_name ?? '—',
                'service_name' => $o->service->service_name ?? '—',
                'status'       => $o->status,
                'payment_status' => $o->payment_status,
                'total_amount' => (float) $o->total_amount,
                'created_at'   => $o->created_at->toDateString(),
            ]);

        return Inertia::render('Dashboard', [
            'recentOrders' => $recentOrders,
            'totalOrders'  => ShopOrder::where('user_id', $user->id)->count(),
        ]);
    }

    // ─── Shops list ───────────────────────────────────────────────────────────

    public function shops(Request $request): Response
    {
        $lat    = $request->float('lat');
        $lng    = $request->float('lng');
        $search = $request->string('search')->trim()->toString();

        $query = Shop::where('status', 'active')
            ->with([
                'services' => fn($q) => $q->where('is_active', true)
                    ->select('id', 'shop_id', 'service_name', 'pricing_model', 'price_per_kg', 'bundle_price', 'bundle_weight_kg'),
            ])
            ->select('id', 'shop_name', 'branch_name', 'phone', 'block_street', 'municipality', 'barangay', 'latitude', 'longitude');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('shop_name', 'like', "%{$search}%")
                    ->orWhere('municipality', 'like', "%{$search}%")
                    ->orWhere('barangay', 'like', "%{$search}%");
            });
        }

        $shops = $query->get()->map(function ($shop) use ($lat, $lng) {
            $data = $shop->toArray();
            $data['distance_km'] = ($lat && $lng && $shop->latitude && $shop->longitude)
                ? $this->haversine($lat, $lng, $shop->latitude, $shop->longitude)
                : null;

            // Lowest starting price from active services
            $minPrice = $shop->services
                ->whereNotNull('price_per_kg')
                ->min('price_per_kg');
            $data['starting_price'] = $minPrice ? (float) $minPrice : null;
            $data['services_count'] = $shop->services->count();

            return $data;
        });

        // Sort by distance when available, else by name
        if ($lat && $lng) {
            $shops = $shops->sortBy(fn($s) => $s['distance_km'] ?? 9999)->values();
        } else {
            $shops = $shops->sortBy('shop_name')->values();
        }

        return Inertia::render('user/Shops', [
            'shops'   => $shops,
            'userLat' => $lat ?: null,
            'userLng' => $lng ?: null,
            'search'  => $search,
        ]);
    }

    // ─── Shop detail ──────────────────────────────────────────────────────────

    public function showShop(Request $request, Shop $shop): Response
    {
        abort_if($shop->status !== 'active', 404);

        $lat = $request->float('lat');
        $lng = $request->float('lng');

        $services = $shop->services()
            ->where('is_active', true)
            ->get([
                'id',
                'shop_id',
                'service_name',
                'description',
                'pricing_model',
                'price_per_kg',
                'bundle_weight_kg',
                'bundle_price',
                'estimated_hours'
            ]);

        $distance = ($lat && $lng && $shop->latitude && $shop->longitude)
            ? $this->haversine($lat, $lng, $shop->latitude, $shop->longitude)
            : null;

        return Inertia::render('user/ShopDetail', [
            'shop' => [
                'id'           => $shop->id,
                'shop_name'    => $shop->shop_name,
                'branch_name'  => $shop->branch_name,
                'phone'        => $shop->phone,
                'block_street' => $shop->block_street,
                'municipality' => $shop->municipality,
                'barangay'     => $shop->barangay,
                'latitude'     => $shop->latitude,
                'longitude'    => $shop->longitude,
                'distance_km'  => $distance,
            ],
            'services' => $services,
            'userLat'  => $lat ?: null,
            'userLng'  => $lng ?: null,
        ]);
    }

    // ─── Haversine formula ────────────────────────────────────────────────────

    private function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $R    = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a    = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return round($R * 2 * atan2(sqrt($a), sqrt(1 - $a)), 1);
    }
}
