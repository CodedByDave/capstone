<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\ShopOrder;
use App\Services\CustomerAgreementService;
use App\Services\CustomerShopCatalogService;
use App\Services\OrderRatingService;
use App\Services\PromotionService;
use App\Services\RiderLoadLimitService;
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
        private readonly RiderLoadLimitService $riderLoad,
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

        $lat = $request->float('lat') ?: null;
        $lng = $request->float('lng') ?: null;

        // Every active shop is open to the customer: the nearest ones when their location is known,
        // with the shop whose QR they scanned always listed first.
        $nearbyShops = $this->shopCards($lat, $lng, $user->referred_shop_id);

        return Inertia::render('Dashboard', [
            'recentOrders' => $recentOrders,
            'totalOrders' => ShopOrder::where('user_id', $user->id)->count(),
            'ratingPrompt' => $this->ratings->promptFor($user),
            'nearbyShops' => $nearbyShops,
            'userLat' => $lat,
            'userLng' => $lng,
            'promotions' => $this->promotions->forCustomerHome($user),
        ]);
    }

    // ─── Shops list ───────────────────────────────────────────────────────────

    public function shops(Request $request): Response
    {
        $lat = $request->float('lat') ?: null;
        $lng = $request->float('lng') ?: null;
        $search = $request->string('search')->trim()->toString();

        return Inertia::render('user/Shops', [
            'shops' => $this->shopCards($lat, $lng, $request->user()->referred_shop_id, $search, limit: null),
            'userLat' => $lat,
            'userLng' => $lng,
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
                // The weight range a rider collects, so the form can explain it before the order is sent.
                'rider_load_limit' => $this->riderLoad->limitsFor($shop),
                'is_open' => $shop->isOpenAt(),
                'is_open_24_7' => (bool) $shop->is_open_24_7,
                'opens_at' => $shop->opens_at ? substr($shop->opens_at, 0, 5) : null,
                'closes_at' => $shop->closes_at ? substr($shop->closes_at, 0, 5) : null,
                'operating_days' => $shop->openDays(),
                'rating_avg' => $rating['average'] ?? null,
                'rating_count' => $rating['count'] ?? 0,
            ],
            'services' => $this->catalog->serviceCards($shop->id, $services),
            'additionalCharges' => $this->catalog->additionalChargeOptions($shop->id),
            'promotions' => $this->promotions->forCustomer($shop->id),
            'customerAgreement' => $this->customerAgreementService->agreementData($shop),
            'userLat' => $lat ?: null,
            'userLng' => $lng ?: null,
        ]);
    }

    // ─── Shop cards ───────────────────────────────────────────────────────────

    /**
     * Active shops as browse cards, nearest first when the customer's location is known, otherwise by
     * name. The shop the customer joined through is always first.
     *
     * @return array<int, array<string, mixed>>
     */
    private function shopCards(?float $lat, ?float $lng, ?int $homeShopId, string $search = '', ?int $limit = 5): array
    {
        $query = Shop::where('status', 'active')
            ->with(['services' => fn ($q) => $q->where('is_active', true)
                ->select('id', 'shop_id', 'service_name', 'pricing_model', 'price_per_kg', 'bundle_price', 'bundle_weight_kg', 'price_per_piece')])
            ->select('id', 'shop_name', 'phone', 'block_street', 'municipality', 'barangay', 'latitude', 'longitude', 'cover_photo');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('shop_name', 'like', "%{$search}%")
                    ->orWhere('municipality', 'like', "%{$search}%")
                    ->orWhere('barangay', 'like', "%{$search}%");
            });
        }

        $found = $query->get();
        $summaries = $this->ratings->summariesFor($found->pluck('id')->all());

        $cards = $found->map(function ($shop) use ($lat, $lng, $summaries) {
            $startingPrice = $this->catalog->startingPrice($shop->services);

            return [
                'id' => $shop->id,
                'shop_name' => $shop->shop_name,
                'phone' => $shop->phone,
                'block_street' => $shop->block_street,
                'municipality' => $shop->municipality,
                'barangay' => $shop->barangay,
                'latitude' => $shop->latitude,
                'longitude' => $shop->longitude,
                'cover_photo' => $this->coverUrl($shop->cover_photo),
                'distance_km' => ($lat && $lng && $shop->latitude && $shop->longitude)
                    ? $this->haversine($lat, $lng, $shop->latitude, $shop->longitude)
                    : null,
                'starting_price' => $startingPrice['amount'],
                'starting_price_unit' => $startingPrice['unit'],
                'services_count' => $shop->services->count(),
                'rating_avg' => $summaries[$shop->id]['average'] ?? null,
                'rating_count' => $summaries[$shop->id]['count'] ?? 0,
            ];
        });

        $cards = ($lat && $lng)
            ? $cards->sortBy(fn ($card) => $card['distance_km'] ?? 9999)
            : $cards->sortBy('shop_name');

        $cards = $cards->values();
        $home = $homeShopId ? $cards->firstWhere('id', $homeShopId) : null;

        if ($limit !== null) {
            // The nearest few, but never at the cost of the customer's own shop.
            $cards = $cards->reject(fn ($card) => $card['id'] === $homeShopId)->take($home ? $limit - 1 : $limit)->values();
        } else {
            $cards = $cards->reject(fn ($card) => $card['id'] === $homeShopId)->values();
        }

        if ($home) {
            $cards->prepend($home);
        }

        return $cards
            ->map(fn ($card) => [...$card, 'is_home_shop' => $card['id'] === $homeShopId])
            ->values()
            ->all();
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
