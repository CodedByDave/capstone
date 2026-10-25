<?php

namespace App\Services\Driver;

use App\Models\Delivery;
use App\Models\Rider;
use App\Models\Shop;
use App\Repositories\DeliveryRepository;
use Illuminate\Support\Collection;

/**
 * What a driver earned, according to the way the shop pays: nothing shown, the delivery fee
 * of each completed delivery, or a flat rate for every day with at least one completed run.
 */
class DriverEarningsService
{
    public function __construct(private readonly DeliveryRepository $deliveries) {}

    /** @return array{mode: string, enabled: bool, amount: float|null} */
    public function today(Rider $rider): array
    {
        $runs = $this->deliveries->finishedBetweenForRider($rider->id, today(), today()->endOfDay());

        return $this->summarize($rider->shop, $runs);
    }

    /**
     * A day-by-day breakdown, newest day first, for the earnings page.
     *
     * @return array{mode: string, enabled: bool, total: float|null, days: list<array<string, mixed>>}
     */
    public function history(Rider $rider, int $days = 14): array
    {
        $shop = $rider->shop;
        $from = today()->subDays($days - 1);
        $runs = $this->deliveries->finishedBetweenForRider($rider->id, $from, today()->endOfDay());

        $rows = $runs->groupBy(fn (Delivery $run) => $run->delivered_at->toDateString())
            ->map(fn (Collection $dayRuns, string $date) => [
                'date' => $date,
                'jobs' => $dayRuns->count(),
                'amount' => $this->summarize($shop, $dayRuns)['amount'],
            ])
            ->sortByDesc('date')
            ->values();

        return [
            'mode' => $shop->driver_pay_mode,
            'enabled' => $shop->driver_pay_mode !== Shop::DRIVER_PAY_NONE,
            'total' => $shop->driver_pay_mode === Shop::DRIVER_PAY_NONE ? null : round((float) $rows->sum('amount'), 2),
            'days' => $rows->all(),
        ];
    }

    /** @param Collection<int, Delivery> $runs completed runs of a single day */
    private function summarize(Shop $shop, Collection $runs): array
    {
        $mode = $shop->driver_pay_mode ?: Shop::DRIVER_PAY_NONE;

        $amount = match ($mode) {
            Shop::DRIVER_PAY_DELIVERY_FEE => round((float) $runs
                ->where('type', Delivery::TYPE_DELIVERY)
                ->sum(fn (Delivery $run) => (float) $run->order?->delivery_fee), 2),
            Shop::DRIVER_PAY_DAILY_RATE => $runs->isEmpty() ? 0.0 : (float) $shop->driver_daily_rate,
            default => null,
        };

        return ['mode' => $mode, 'enabled' => $mode !== Shop::DRIVER_PAY_NONE, 'amount' => $amount];
    }
}
