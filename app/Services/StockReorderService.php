<?php

namespace App\Services;

use App\Repositories\ShopAnalyticsRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/** Estimates how long each supply will last and how much of it to order. */
class StockReorderService
{
    /** Days of recorded use the daily rate is averaged over. */
    public const WINDOW_DAYS = 30;

    /** Extra days of cover kept on top of the supplier lead time before a reorder is flagged. */
    public const SAFETY_DAYS = 3;

    /** Days of use an order should cover once it arrives, after the lead time. */
    public const COVER_DAYS = 7;

    /** Lead time assumed for items without a supplier. */
    public const DEFAULT_LEAD_TIME_DAYS = 3;

    public function __construct(private readonly ShopAnalyticsRepository $analytics) {}

    /**
     * Reorder figures for every active item with recorded use, keyed by inventory id.
     *
     * suggested_quantity = daily use × (lead time + cover days) − current stock, never below zero.
     *
     * @return Collection<int, array{id: int, name: string, unit: ?string, quantity: int, daily_use: float, days_left: float, lead_time_days: int, needs_reorder: bool, suggested_quantity: int}>
     */
    public function forShop(int $shopId, ?Carbon $now = null): Collection
    {
        $now ??= Carbon::now();

        return $this->analytics->consumptionSince($shopId, $now->copy()->subDays(self::WINDOW_DAYS))
            ->map(function (array $item) {
                $daily = $item['used'] / self::WINDOW_DAYS;
                $lead = $item['lead_time_days'] ?? self::DEFAULT_LEAD_TIME_DAYS;
                $daysLeft = $item['quantity'] / $daily;

                return [
                    'id' => $item['id'],
                    'name' => $item['name'],
                    'unit' => $item['unit'],
                    'quantity' => $item['quantity'],
                    'daily_use' => round($daily, 2),
                    'days_left' => round($daysLeft, 1),
                    'lead_time_days' => $lead,
                    'needs_reorder' => $daysLeft <= $lead + self::SAFETY_DAYS,
                    'suggested_quantity' => max(0, (int) ceil($daily * ($lead + self::COVER_DAYS) - $item['quantity'])),
                ];
            })
            ->keyBy('id');
    }

    /** Only the items that should be reordered now, keyed by inventory id. */
    public function dueForShop(int $shopId, ?Carbon $now = null): Collection
    {
        return $this->forShop($shopId, $now)->filter(fn (array $item) => $item['needs_reorder']);
    }
}
