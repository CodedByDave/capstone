<?php

namespace App\Http\Middleware;

use App\Models\Shop;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureShopNotArchived
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()->role === 'owner') {
            $archivedShop = Shop::withTrashed()
                ->where('owner_id', Auth::id())
                ->first();

            if ($archivedShop && $archivedShop->trashed()) {
                \Illuminate\Support\Facades\Log::info('Archived shop detected, logging out', ['user_id' => Auth::id()]);
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                if ($request->header('X-Inertia')) {
                    return response()->json(['url' => route('login')], 409)
                        ->header('X-Inertia-Location', route('login'));
                }

                return redirect()->route('login')->with('toast', [
                    'type' => 'error',
                    'message' => 'Your shop has been archived. Please contact support.',
                ]);
            }
        }

        return $next($request);
    }
}
