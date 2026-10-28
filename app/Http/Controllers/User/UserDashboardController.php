<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\ShopOrder;
use App\Services\CustomerAgreementService;
use App\Services\CustomerShopCatalogService;
use App\Services\OrderRatingService;
use App\Services\PromotionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserDashboardController extends Controller
{
    public function __construct(
        private readonly CustomerAgreementService $customerAgreementService,
        private readonly CustomerShopCatalogService $catalog,
        private readonly OrderRatingService $ratings,
        private readonly PromotionService $promotions,
    ) {}

    // ─── Dashboard ────────────────────────────────────────────────────────────

    public function index(Request $request): Response
    {
        $user = auth()->user();
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

        // A customer only sees the shop whose QR they joined through.
        $nearbyShops = [];
        if ($user->referred_shop_id) {
            $nearbyShops = Shop::where('status', 'active')
                ->where('id', $user->referred_shop_id)
                ->with(['services' => fn ($q) => $q->where('is_active', true)
                    ->select('id', 'shop_id', 'service_name', 'pricing_model', 'price_per_kg', 'bundle_price')])
                ->select('id', 'shop_name', 'municipality', 'barangay', 'latitude', 'longitude', 'cover_photo')
                ->get()
                ->map(function ($shop) {
                    $startingPrice = $this->catalog->startingPrice($shop->services);

                    return [
                        'id' => $shop->id,
                        'shop_name' => $shop->shop_name,
                        'municipality' => $shop->municipality,
                        'barangay' => $shop->barangay,
                        'cover_photo' => $this->coverUrl($shop->cover_photo),
                        'distance_km' => null,
                        'starting_price' => $startingPrice['amount'],
                        'starting_price_unit' => $startingPrice['unit'],
                        'services_count' => $shop->services->count(),
                    ];
                })
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
            'promotions' => $user->referred_shop_id
                ? $this->promotions->forCustomer((int) $user->referred_shop_id)
                : [],
        ]);
    }

    // ─── Shops list ───────────────────────────────────────────────────────────

    public function shops(Request $request): RedirectResponse
    {
        // Customers have a single shop, so the shop list is replaced by that shop's page.
        $referredShopId = $request->user()->referred_shop_id;

        if (! $referredShopId) {
            return redirect()->route('user.dashboard');
        }

        return redirect()->route('user.shops.show', $referredShopId);
    }

    // ─── Shop detail ──────────────────────────────────────────────────────────

    public function showShop(Request $request, Shop $shop): Response
    {
        abort_if($shop->status !== 'active', 404);
        abort_if($request->user()->referred_shop_id !== $shop->id, 404);

        $lat = $request->float('lat');
        $lng = $request->float('lng');

        $services = $shop->services()
            ->where('is_active', true)
            ->with('supplies:id,name,unit')
            ->get([
                'id',
                'shop_id',
                'service_name',
                'category',
                'description',
                'photo_path',
                'pricing_model',
                'price_per_kg',
                'bundle_weight_kg',
                'bundle_price',
                'price_per_piece',
            ]);

        $distance = ($lat && $lng && $shop->latitude && $shop->longitude)
            ? $this->haversine($lat, $lng, $shop->latitude, $shop->longitude)
            : null;

        $rating = $this->ratings->summariesFor([$shop->id])[$shop->id] ?? null;

        return Inertia::render('user/ShopDetail', [
            'shop' => [
                'id' => $shop->id,
                'shop_name' => $shop->shop_name,
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
            'services' => $this->catalog->serviceCards($shop->id, $services),
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
