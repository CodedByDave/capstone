<?php

namespace App\Http\Middleware;

use App\Services\ShopSetupService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureShopSetupComplete
{
    public function __construct(private readonly ShopSetupService $setup) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user?->role === 'owner'
            && ! $request->routeIs('shop.setup.*')
            && ! $request->routeIs('payment.success', 'payment.cancel', 'shop.payment.pay')
            && $this->setup->needsSetup($user)) {
            return redirect()->route('shop.setup.show')->with('toast', [
                'type' => 'info',
                'message' => 'Complete your shop setup before opening the dashboard.',
            ]);
        }

        return $next($request);
    }
}
