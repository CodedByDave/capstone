<?php

namespace App\Http\Middleware;

use App\Models\Delivery;
use App\Models\Rider;
use App\Models\ShopOrder;
use App\Repositories\EmployeeRepository;
use App\Repositories\ShopRepository;
use App\Services\ShopSetupService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureShopCapability
{
    public function __construct(
        private readonly ShopSetupService $setup,
        private readonly ShopRepository $shops,
        private readonly EmployeeRepository $employees,
    ) {}

    public function handle(Request $request, Closure $next, string $capability): Response
    {
        if ($capability === 'pickup' && $request->input('pickup_type') !== 'pickup') {
            return $next($request);
        }

        $shopId = $this->resolveShopId($request);
        $actorShopId = $this->resolveActorShopId($request);

        if ($actorShopId !== null && $shopId !== null) {
            abort_if($actorShopId !== $shopId, 403, 'This record belongs to another shop.');
        }

        // A disabled delivery service may finish an already-created delivery,
        // but it cannot create new deliveries or riders.
        if ($capability === 'delivery'
            && $request->route('delivery') instanceof Delivery
            && $request->isMethod('PATCH')) {
            return $next($request);
        }

        abort_if(
            $shopId === null || ! $this->setup->shopSupports($shopId, $capability),
            403,
            $this->message($capability),
        );

        return $next($request);
    }

    private function resolveShopId(Request $request): ?int
    {
        if ($request->route('order') instanceof ShopOrder) {
            return $request->route('order')->shop_id;
        }

        if ($request->route('delivery') instanceof Delivery) {
            return $request->route('delivery')->shop_id;
        }

        if ($request->route('rider') instanceof Rider) {
            return $request->route('rider')->shop_id;
        }

        if ($request->filled('shop_id')) {
            return (int) $request->input('shop_id');
        }

        return $this->resolveActorShopId($request);
    }

    private function resolveActorShopId(Request $request): ?int
    {
        $user = $request->user();
        if ($user?->role === 'owner') {
            return $this->shops->findByOwnerId($user->id)?->id;
        }

        if ($user?->role === 'staff') {
            $shopId = $this->employees->query()->where('user_id', $user->id)->value('shop_id');

            return $shopId === null ? null : (int) $shopId;
        }

        return null;
    }

    private function message(string $capability): string
    {
        return match ($capability) {
            'branches' => 'Branch management is not enabled for this shop.',
            'pickup' => 'Customer pickup is not offered by this shop.',
            'delivery' => 'Customer delivery is not offered by this shop.',
            default => 'This shop capability is not enabled.',
        };
    }
}
