<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\ShopOrder;
use App\Services\CustomerAgreementService;
use App\Services\CustomerShopCatalogService;
use App\Services\OrderRatingService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserDashboardController extends Controller
{
    public function __construct(
        private readonly CustomerAgreementService $customerAgreementService,
        private readonly CustomerShopCatalogService $catalog,
        private readonly OrderRatingService $ratings,
    ) {}

    // ─── Dashboard ────────────────────────────────────────────────────────────

    public function index(Request $request): Response
    {
        $user = auth()->user();
        $lat = $request->float('lat') ?: null;
        $lng = $request->float('lng') ?: null;

        $recentOrders = ShopOrder::where('user_id', $user->id)
            ->with(['shop:id,shop_name,municipality,barangay', 'service:id,service_name'])
            ->latest()
            ->take(3)
            ->get()
            ->map(fn ($o) => [
                'id' => $o->id,
                'payment_public_id' => $o->payment_public_id,
                'order_number' => $o->order_number,
                'shop_name' => $o->shop->shop_name ?? '—',
                'service_name' => $o->service->service_name ?? '—',
                'status' => $o->status,
                'payment_status' => $o->payment_status,
                'total_amount' => (float) $o->total_amount,
                'created_at' => $o->created_at->toDateString(),
            ]);

        // Nearby shops (only when coordinates are provided)
        $nearbyShops = [];
        if ($lat && $lng) {
            $nearbyShops = Shop::where('status', 'active')
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->with(['services' => fn ($q) => $q->where('is_active', true)
                    ->select('id', 'shop_id', 'service_name', 'pricing_model', 'price_per_kg', 'bundle_price')])
                ->select('id', 'shop_name', 'branch_name', 'municipality', 'barangay', 'latitude', 'longitude', 'cover_photo')
                ->get()
                ->map(function ($shop) use ($lat, $lng) {
                    $startingPrice = $this->catalog->startingPrice($shop->services);

                    return [
                        'id' => $shop->id,
                        'shop_name' => $shop->shop_name,
                        'branch_name' => $shop->branch_name,
                        'municipality' => $shop->municipality,
                        'barangay' => $shop->barangay,
                        'cover_photo' => $this->coverUrl($shop->cover_photo),
                        'distance_km' => $this->haversine($lat, $lng, $shop->latitude, $shop->longitude),
                        'starting_price' => $startingPrice['amount'],
                        'starting_price_unit' => $startingPrice['unit'],
                        'services_count' => $shop->services->count(),
                    ];
                })
                ->sortBy('distance_km')
                ->take(5)
                ->values()
                ->toArray();

            $summaries = $this->ratings->summariesFor(array_column($nearbyShops, 'id'));
            $nearbyShops = array_map(fn (array $shop) => $shop + [
                'rating_avg' => $summaries[$shop['id']]['average'] ?? null,
                'rating_count' => $summaries[$shop['id']]['count'] ?? 0,
            ], $nearbyShops);
        }

        return Inertia::render('Dashboard', [
            'recentOrders' => $recentOrders,
            'totalOrders' => ShopOrder::where('user_id', $user->id)->count(),
            'ratingPrompt' => $this->ratings->promptFor($user),
            'nearbyShops' => $nearbyShops,
            'userLat' => $lat,
            'userLng' => $lng,
        ]);
    }

    // ─── Shops list ───────────────────────────────────────────────────────────

    public function shops(Request $request): Response
    {
        $lat = $request->float('lat');
        $lng = $request->float('lng');
        $search = $request->string('search')->trim()->toString();

        $query = Shop::where('status', 'active')
            ->with([
                'services' => fn ($q) => $q->where('is_active', true)
                    ->select('id', 'shop_id', 'service_name', 'pricing_model', 'price_per_kg', 'bundle_price', 'bundle_weight_kg'),
            ])
            ->select('id', 'shop_name', 'branch_name', 'phone', 'block_street', 'municipality', 'barangay', 'latitude', 'longitude', 'cover_photo');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('shop_name', 'like', "%{$search}%")
                    ->orWhere('municipality', 'like', "%{$search}%")
                    ->orWhere('barangay', 'like', "%{$search}%");
            });
        }

        $found = $query->get();
        $summaries = $this->ratings->summariesFor($found->pluck('id')->all());

        $shops = $found->map(function ($shop) use ($lat, $lng, $summaries) {
            $data = $shop->toArray();
            $data['cover_photo'] = $this->coverUrl($shop->cover_photo);
            $data['distance_km'] = ($lat && $lng && $shop->latitude && $shop->longitude)
                ? $this->haversine($lat, $lng, $shop->latitude, $shop->longitude)
                : null;

            $startingPrice = $this->catalog->startingPrice($shop->services);
            $data['starting_price'] = $startingPrice['amount'];
            $data['starting_price_unit'] = $startingPrice['unit'];
            $data['services_count'] = $shop->services->count();
            $data['rating_avg'] = $summaries[$shop->id]['average'] ?? null;
            $data['rating_count'] = $summaries[$shop->id]['count'] ?? 0;

            return $data;
        });

        // Sort by distance when available, else by name
        if ($lat && $lng) {
            $shops = $shops->sortBy(fn ($s) => $s['distance_km'] ?? 9999)->values();
        } else {
            $shops = $shops->sortBy('shop_name')->values();
        }

        return Inertia::render('user/Shops', [
            'shops' => $shops,
            'userLat' => $lat ?: null,
            'userLng' => $lng ?: null,
            'search' => $search,
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
                'category',
                'description',
                'pricing_model',
                'price_per_kg',
                'bundle_weight_kg',
                'bundle_price',
                'estimated_hours',
            ]);

        $distance = ($lat && $lng && $shop->latitude && $shop->longitude)
            ? $this->haversine($lat, $lng, $shop->latitude, $shop->longitude)
            : null;

        $rating = $this->ratings->summariesFor([$shop->id])[$shop->id] ?? null;

        return Inertia::render('user/ShopDetail', [
            'shop' => [
                'id' => $shop->id,
                'shop_name' => $shop->shop_name,
                'branch_name' => $shop->branch_name,
                'phone' => $shop->phone,
                'block_street' => $shop->block_street,
                'municipality' => $shop->municipality,
                'barangay' => $shop->barangay,
                'latitude' => $shop->latitude,
                'longitude' => $shop->longitude,
                'cover_photo' => $this->coverUrl($shop->cover_photo),
                'payment_options' => $this->catalog->paymentOptions($shop),
                'distance_km' => $distance,
                'offers_pickup' => (bool) $shop->offers_pickup,
                'offers_delivery' => $shop->canDeliver(),
                'rating_avg' => $rating['average'] ?? null,
                'rating_count' => $rating['count'] ?? 0,
            ],
            'services' => $services,
            'customerAgreement' => $this->customerAgreementService->agreementData($shop),
            'userLat' => $lat ?: null,
            'userLng' => $lng ?: null,
        ]);
    }

    // ─── URL helpers ──────────────────────────────────────────────────────────

    private function coverUrl(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        return str_starts_with($value, 'http') ? $value : \Storage::disk('public')->url($value);
    }

    // ─── Haversine formula ────────────────────────────────────────────────────

    private function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $R = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return round($R * 2 * atan2(sqrt($a), sqrt(1 - $a)), 1);
    }
}
