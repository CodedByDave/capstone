<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateShopRequest;
<<<<<<< HEAD
=======
use App\Models\Order;
use App\Models\Shop;
use App\Models\User;
use App\Services\ActivityLogService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
>>>>>>> 7b1b8656 (feat(admin): added issue reports features for system users and RBAC for giving users access what they can do)
use Inertia\Inertia;
use App\Models\Shop;
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

<<<<<<< HEAD
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
=======
        $allowedSorts = [
            'shop_name', 'owner', 'health_status', 'compliance_status',
            'subscription', 'last_activity_at', 'status', 'created_at',
        ];
        $sortBy = in_array($request->string('sort_by')->toString(), $allowedSorts, true)
            ? $request->string('sort_by')->toString()
            : 'created_at';
        $sortDirection = $request->string('sort_direction')->toString() === 'asc' ? 'asc' : 'desc';

        $this->applySorting($query, $sortBy, $sortDirection);

        $shops = $query
            ->orderBy('shops.id', $sortDirection)
            ->paginate(min(max($request->integer('per_page', 15), 5), 100))
            ->withQueryString()
            ->through(fn (Shop $shop) => $this->serializeShop($shop));

        return Inertia::render('admin/shop/Index', [
            'shops' => $shops,
            'stats' => $this->stats(),
            'filters' => $request->only([
                'search', 'status', 'plan', 'compliance', 'activity',
                'subscription', 'trashed', 'per_page', 'sort_by',
                'sort_direction',
            ]),
        ]);
    }

    public function exportCsv(Request $request)
    {
        $query = $request->boolean('trashed')
            ? Shop::onlyTrashed()->with(['owner', 'latestOrder'])
            : Shop::query()->with(['owner', 'latestOrder']);
        $this->applyFilters($query, $request);
        $allowedSorts = [
            'shop_name', 'owner', 'health_status', 'compliance_status',
            'subscription', 'last_activity_at', 'status', 'created_at',
        ];
        $sortBy = in_array($request->string('sort_by')->toString(), $allowedSorts, true)
            ? $request->string('sort_by')->toString()
            : 'created_at';
        $sortDirection = $request->string('sort_direction')->toString() === 'asc' ? 'asc' : 'desc';
        $this->applySorting($query, $sortBy, $sortDirection);

        $shops = $query->orderBy('shops.id', $sortDirection)->get()->map(
            fn (Shop $shop) => $this->serializeShop($shop),
        );

        return response()->streamDownload(function () use ($shops) {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, [
                'shop_name', 'owner', 'owner_email', 'phone', 'municipality',
                'barangay', 'status', 'plan', 'subscription_expires_at',
                'compliance', 'health', 'last_activity_at', 'bir_expiry_date',
                'dti_expiry_date', 'mayors_expiry_date', 'sanitary_expiry_date',
            ], ',', '"', '');

            foreach ($shops as $shop) {
                fputcsv($output, [
                    $shop['shop_name'], $shop['owner']['name'] ?? null,
                    $shop['owner']['email'] ?? null, $shop['phone'],
                    $shop['municipality'], $shop['barangay'], $shop['status'],
                    $shop['subscription_plan'], $shop['expires_at'],
                    $shop['compliance_status'], $shop['health_status'],
                    $shop['last_activity_at'], $shop['bir_expiry_date'],
                    $shop['dti_expiry_date'], $shop['mayors_expiry_date'],
                    $shop['sanitary_expiry_date'],
                ], ',', '"', '');
            }
            fclose($output);
        }, 'shops-health-compliance-'.now()->format('Y-m-d-His').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
>>>>>>> 7b1b8656 (feat(admin): added issue reports features for system users and RBAC for giving users access what they can do)
        ]);
    }

    public function show(Shop $shop)
    {
        $shop->load('owner');

        $latestOrder = \App\Models\Order::where('user_id', $shop->owner_id)
            ->where('status', 'paid')
            ->latest()
            ->first();

        return Inertia::render('admin/shop/Show', [
            'shop' => array_merge($shop->toArray(), [
                'subscription_plan' => $latestOrder?->subscription_plan ?? null,
                'expires_at'        => $latestOrder?->expires_at ?? null,
                'is_expired'        => $latestOrder?->expires_at
                    ? \Carbon\Carbon::parse($latestOrder->expires_at)->isPast()
                    : true,
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

<<<<<<< HEAD
        return redirect()->back()
            ->with('toast', ['type' => 'success', 'message' => "{$shop->shop_name} has been re-enabled."]);
=======
        return back()->with('toast', ['type' => 'success', 'message' => "{$shop->shop_name} has been re-enabled."]);
    }

    private function applyFilters(Builder $query, Request $request): void
    {
        $query->when($request->filled('search'), function (Builder $query) use ($request) {
            $search = $request->string('search');
            $query->where(function (Builder $query) use ($search) {
                $query->where('shop_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhereHas('owner', fn (Builder $owner) => $owner
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%"));
            });
        });
        $query->when($request->filled('status'), fn (Builder $query) => $query->where('status', $request->status));

        if ($request->filled('plan')) {
            $request->plan === 'none'
                ? $query->whereDoesntHave('owner.orders', fn (Builder $orders) => $orders->activeSubscription())
                : $query->whereHas('owner.orders', fn (Builder $orders) => $orders
                    ->activeSubscription()->where('plan_name', $request->plan));
        }

        $today = today();
        $soon = today()->addDays(30);
        if ($request->compliance === 'expired') {
            $query->where(fn (Builder $query) => collect(self::PERMIT_COLUMNS)
                ->each(fn ($column) => $query->orWhereDate($column, '<', $today)));
        } elseif ($request->compliance === 'expiring') {
            $query->where(fn (Builder $query) => collect(self::PERMIT_COLUMNS)
                ->each(fn ($column) => $query->orWhereBetween($column, [$today, $soon])));
        } elseif ($request->compliance === 'incomplete') {
            $query->where(fn (Builder $query) => collect(self::REQUIRED_PERMIT_COLUMNS)
                ->each(fn ($column) => $query->orWhereNull($column)));
        } elseif ($request->compliance === 'compliant') {
            foreach (self::REQUIRED_PERMIT_COLUMNS as $column) {
                $query->whereNotNull($column)->whereDate($column, '>', $soon);
            }
            $query->where(fn (Builder $query) => $query
                ->whereNull('sanitary_expiry_date')
                ->orWhereDate('sanitary_expiry_date', '>', $soon));
        }

        if ($request->activity === 'inactive') {
            $query->where(fn (Builder $query) => $query
                ->whereNull('last_activity_at')
                ->orWhere('last_activity_at', '<', now()->subDays(30)));
        }

        if ($request->subscription === 'expired') {
            $query->whereHas('owner.orders', fn (Builder $orders) => $orders
                ->activeSubscription()
                ->where('expires_at', '<', now()));
        } elseif ($request->subscription === 'expiring') {
            $query->whereHas('owner.orders', fn (Builder $orders) => $orders
                ->activeSubscription()
                ->whereBetween('expires_at', [now(), now()->addDays(7)]));
        }
    }

    private function applySorting(Builder $query, string $sortBy, string $direction): void
    {
        if ($sortBy === 'owner') {
            $query->orderBy(
                User::query()
                    ->select('name')
                    ->whereColumn('users.id', 'shops.owner_id')
                    ->limit(1),
                $direction,
            );

            return;
        }

        if ($sortBy === 'subscription') {
            $query->orderBy(
                Order::query()
                    ->select('plan_name')
                    ->whereColumn('orders.user_id', 'shops.owner_id')
                    ->activeSubscription()
                    ->latest('orders.id')
                    ->limit(1),
                $direction,
            );

            return;
        }

        if ($sortBy === 'compliance_status') {
            [$expression, $bindings] = $this->complianceSortExpression();
            $query->orderByRaw("{$expression} {$direction}", $bindings);

            return;
        }

        if ($sortBy === 'health_status') {
            [$expression, $bindings] = $this->healthSortExpression();
            $query->orderByRaw("{$expression} {$direction}", $bindings);

            return;
        }

        $query->orderBy("shops.{$sortBy}", $direction);
    }

    private function complianceSortExpression(): array
    {
        $today = today()->toDateString();
        $soon = today()->addDays(30)->toDateString();
        $expired = collect(self::PERMIT_COLUMNS)
            ->map(fn (string $column) => "shops.{$column} < ?")
            ->implode(' OR ');
        $missing = collect(self::REQUIRED_PERMIT_COLUMNS)
            ->map(fn (string $column) => "shops.{$column} IS NULL")
            ->implode(' OR ');
        $expiring = collect(self::PERMIT_COLUMNS)
            ->map(fn (string $column) => "shops.{$column} BETWEEN ? AND ?")
            ->implode(' OR ');

        return [
            "CASE WHEN ({$expired}) THEN 'expired' WHEN ({$missing}) THEN 'incomplete' WHEN ({$expiring}) THEN 'expiring' ELSE 'compliant' END",
            [
                ...array_fill(0, count(self::PERMIT_COLUMNS), $today),
                ...collect(self::PERMIT_COLUMNS)->flatMap(fn () => [$today, $soon])->all(),
            ],
        ];
    }

    private function healthSortExpression(): array
    {
        $today = today()->toDateString();
        $soon = today()->addDays(30)->toDateString();
        $inactiveBefore = now()->subDays(30)->toDateTimeString();
        $expiredPermits = collect(self::PERMIT_COLUMNS)
            ->map(fn (string $column) => "shops.{$column} < ?")
            ->implode(' OR ');
        $missingPermits = collect(self::REQUIRED_PERMIT_COLUMNS)
            ->map(fn (string $column) => "shops.{$column} IS NULL")
            ->implode(' OR ');
        $expiringPermits = collect(self::PERMIT_COLUMNS)
            ->map(fn (string $column) => "shops.{$column} BETWEEN ? AND ?")
            ->implode(' OR ');
        $latestSubscriptionExpiry = "(
            SELECT orders.expires_at FROM orders
            WHERE orders.user_id = shops.owner_id
              AND (orders.status = 'paid' OR (orders.status = 'approved' AND orders.is_trial = 1))
            ORDER BY orders.id DESC LIMIT 1
        )";

        return [
            "CASE
                WHEN ({$expiredPermits}) OR {$latestSubscriptionExpiry} < ? OR shops.status = 'disabled' THEN 'critical'
                WHEN ({$missingPermits}) OR ({$expiringPermits}) OR shops.last_activity_at IS NULL OR shops.last_activity_at < ? OR shops.status = 'pending' THEN 'attention'
                ELSE 'healthy'
            END",
            [
                ...array_fill(0, count(self::PERMIT_COLUMNS), $today),
                now()->toDateTimeString(),
                ...collect(self::PERMIT_COLUMNS)->flatMap(fn () => [$today, $soon])->all(),
                $inactiveBefore,
            ],
        ];
    }

    private function serializeShop(Shop $shop): array
    {
        $latestOrder = $shop->latestOrder;
        $permitDates = collect(self::PERMIT_COLUMNS)->mapWithKeys(
            fn ($column) => [$column => $shop->{$column}?->toDateString()],
        );
        $missing = collect(self::REQUIRED_PERMIT_COLUMNS)
            ->contains(fn ($column) => $shop->{$column} === null);
        $expiredPermit = $permitDates->filter()->contains(fn ($date) => Carbon::parse($date)->isPast());
        $expiringPermit = $permitDates->filter()->contains(
            fn ($date) => Carbon::parse($date)->between(today(), today()->addDays(30)),
        );
        $compliance = $expiredPermit ? 'expired' : ($missing ? 'incomplete' : ($expiringPermit ? 'expiring' : 'compliant'));
        $subscriptionExpired = $latestOrder?->expires_at ? Carbon::parse($latestOrder->expires_at)->isPast() : false;
        $inactive = ! $shop->last_activity_at || $shop->last_activity_at->lt(now()->subDays(30));
        $health = ($expiredPermit || $subscriptionExpired || $shop->status === 'disabled')
            ? 'critical'
            : (($missing || $expiringPermit || $inactive || $shop->status === 'pending') ? 'attention' : 'healthy');

        return array_merge([
            'id' => $shop->id,
            'public_id' => $shop->public_id,
            'shop_name' => $shop->shop_name,
            'branch_name' => $shop->branch_name,
            'phone' => $shop->phone,
            'municipality' => $shop->municipality,
            'barangay' => $shop->barangay,
            'status' => $shop->status,
            'disable_reason' => $shop->disable_reason,
            'created_at' => $shop->created_at,
            'deleted_at' => $shop->deleted_at,
            'last_activity_at' => $shop->last_activity_at,
            'owner' => $shop->owner ? $shop->owner->only(['id', 'name', 'email']) : null,
            'subscription_plan' => $latestOrder?->plan_name,
            'expires_at' => $latestOrder?->expires_at,
            'is_expired' => $subscriptionExpired,
            'is_expiring_soon' => $latestOrder?->expires_at
                ? Carbon::parse($latestOrder->expires_at)->between(now(), now()->addDays(7))
                : false,
            'compliance_status' => $compliance,
            'health_status' => $health,
            'is_inactive' => $inactive,
        ], $permitDates->all());
    }

    private function stats(): array
    {
        $today = today()->toDateString();
        $soon = today()->addDays(30)->toDateString();
        $expiredPermitSql = collect(self::PERMIT_COLUMNS)
            ->map(fn ($column) => "{$column} < ?")
            ->implode(' OR ');
        $expiringPermitSql = collect(self::PERMIT_COLUMNS)
            ->map(fn ($column) => "{$column} BETWEEN ? AND ?")
            ->implode(' OR ');
        $complianceIssueSql = collect(self::REQUIRED_PERMIT_COLUMNS)
            ->map(fn ($column) => "{$column} IS NULL")
            ->merge(collect(self::PERMIT_COLUMNS)->map(fn ($column) => "{$column} <= ?"))
            ->implode(' OR ');
        $summary = Shop::query()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN DATE(created_at) = ? THEN 1 ELSE 0 END) as today', [$today])
            ->selectRaw("SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active")
            ->selectRaw("SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending")
            ->selectRaw("SUM(CASE WHEN status = 'disabled' THEN 1 ELSE 0 END) as disabled")
            ->selectRaw("SUM(CASE WHEN ({$expiredPermitSql}) THEN 1 ELSE 0 END) as expired_permits", array_fill(0, count(self::PERMIT_COLUMNS), $today))
            ->selectRaw(
                "SUM(CASE WHEN ({$expiringPermitSql}) THEN 1 ELSE 0 END) as expiring_permits",
                collect(self::PERMIT_COLUMNS)->flatMap(fn () => [$today, $soon])->all(),
            )
            ->selectRaw(
                "SUM(CASE WHEN ({$complianceIssueSql}) THEN 1 ELSE 0 END) as compliance_issues",
                array_fill(0, count(self::PERMIT_COLUMNS), $soon),
            )
            ->selectRaw('SUM(CASE WHEN last_activity_at IS NULL OR last_activity_at < ? THEN 1 ELSE 0 END) as inactive', [now()->subDays(30)])
            ->first();

        $expiredSubscriptionQuery = fn (Builder $orders) => $orders
            ->activeSubscription()
            ->where('expires_at', '<', now());
        $expiredSubscriptions = Shop::whereHas('owner.orders', $expiredSubscriptionQuery)->count();
        $needsAttention = Shop::where(function (Builder $query) use ($expiredSubscriptionQuery) {
            $query->where('status', 'disabled')
                ->orWhereNull('last_activity_at')
                ->orWhere('last_activity_at', '<', now()->subDays(30))
                ->orWhereHas('owner.orders', $expiredSubscriptionQuery);
        })->count();

        return [
            'today' => (int) $summary->today,
            'total' => (int) $summary->total,
            'active' => (int) $summary->active,
            'pending' => (int) $summary->pending,
            'disabled' => (int) $summary->disabled,
            'archived' => Shop::onlyTrashed()->count(),
            'expired_permits' => (int) $summary->expired_permits,
            'expiring_permits' => (int) $summary->expiring_permits,
            'compliance_issues' => (int) $summary->compliance_issues,
            'inactive' => (int) $summary->inactive,
            'expired_subscriptions' => $expiredSubscriptions,
            'needs_attention' => $needsAttention,
        ];
    }

    private function logAction(Shop $shop, string $action, ?array $changes = null): void
    {
        $this->activityLogService->log(
            $shop,
            $action,
            $changes ?? ['target_shop' => ['name' => $shop->shop_name, 'id' => $shop->id]],
            module: 'Shop Management',
        );
>>>>>>> 7b1b8656 (feat(admin): added issue reports features for system users and RBAC for giving users access what they can do)
    }
}
