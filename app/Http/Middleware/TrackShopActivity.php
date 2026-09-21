<?php

namespace App\Http\Middleware;

use App\Models\Employee;
use App\Models\Shop;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackShopActivity
{
    private const READ_ACTIVITY_INTERVAL_MINUTES = 5;

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response->getStatusCode() >= 400 || ! $request->user()) {
            return $response;
        }

        $shopId = $this->resolveShopId($request);
        if (! $shopId) {
            return $response;
        }

        $activityAt = now();
        $query = Shop::query()->whereKey($shopId);

        if ($request->isMethod('GET') || $request->isMethod('HEAD')) {
            $query->where(function ($query) use ($activityAt): void {
                $query->whereNull('last_activity_at')
                    ->orWhere('last_activity_at', '<', $activityAt->copy()->subMinutes(self::READ_ACTIVITY_INTERVAL_MINUTES));
            });
        }

        $query->update(['last_activity_at' => $activityAt]);

        return $response;
    }

    private function resolveShopId(Request $request): ?int
    {
        $user = $request->user();

        if ($user->isOwner()) {
            return Shop::where('owner_id', $user->id)->value('id');
        }

        if (! $user->isStaff()) {
            return null;
        }

        return $user->shop_id
            ?? Employee::where('user_id', $user->id)->value('shop_id');
    }
}
