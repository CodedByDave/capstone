<?php

namespace App\Services;

use App\Repositories\ShopAnalyticsRepository;
use Illuminate\Support\Carbon;

/** Projects expected orders for the coming week and compares them with who is scheduled. */
class StaffingForecastService
{
    /** Past weeks of the same weekday averaged into a day's forecast. */
    public const HISTORY_WEEKS = 6;

    /** Days ahead that are forecast, starting tomorrow. */
    public const HORIZON_DAYS = 7;

    /** Orders one employee is expected to handle in a day. */
    public const ORDERS_PER_STAFF = 12;

    /** Orders needed in the history window before a forecast is trusted. */
    public const MIN_HISTORY_ORDERS = 10;

    /** Extra scheduled employees beyond the need before a day counts as overstaffed. */
    public const SURPLUS_TOLERANCE = 1;

    public const UNDERSTAFFED = 'understaffed';

    public const OVERSTAFFED = 'overstaffed';

    public const BALANCED = 'balanced';

    public function __construct(private readonly ShopAnalyticsRepository $analytics) {}

    /**
     * One entry per upcoming day; empty when the shop has too little order history.
     *
     * expected_orders is the mean order count of the same weekday over the last HISTORY_WEEKS weeks
     * (days without orders count as zero), rounded to whole orders.
     *
     * @return list<array{date: string, day: string, expected_orders: int, required_staff: int, scheduled_staff: int, status: string}>
     */
    public function forShop(int $shopId, ?Carbon $now = null): array
    {
        $today = ($now ?? Carbon::now())->copy()->startOfDay();
        $since = $today->copy()->subWeeks(self::HISTORY_WEEKS);
        $counts = $this->analytics->dailyOrderCounts($shopId, $since, $today->copy()->subSecond());

        if ($counts->sum() < self::MIN_HISTORY_ORDERS) {
            return [];
        }

        $scheduled = $this->analytics->scheduledStaffByDay($shopId);

        return collect(range(1, self::HORIZON_DAYS))->map(function (int $ahead) use ($today, $counts, $scheduled) {
            $date = $today->copy()->addDays($ahead);
            $expected = $this->expectedOrders($date, $today, $counts);
            $required = $expected > 0 ? max(1, (int) ceil($expected / self::ORDERS_PER_STAFF)) : 0;
            $staff = (int) ($scheduled[$date->format('l')] ?? 0);

            return [
                'date' => $date->toDateString(),
                'day' => $date->format('D'),
                'expected_orders' => $expected,
                'required_staff' => $required,
                'scheduled_staff' => $staff,
                'status' => match (true) {
                    $staff < $required => self::UNDERSTAFFED,
                    $staff > $required + self::SURPLUS_TOLERANCE => self::OVERSTAFFED,
                    default => self::BALANCED,
                },
            ];
        })->all();
    }

    private function expectedOrders(Carbon $date, Carbon $today, $counts): int
    {
        $samples = collect(range(1, self::HISTORY_WEEKS))
            ->map(fn (int $week) => $date->copy()->subWeeks($week))
            ->filter(fn (Carbon $past) => $past->lt($today))
            ->map(fn (Carbon $past) => (int) ($counts[$past->toDateString()] ?? 0));

        return $samples->isEmpty() ? 0 : (int) round($samples->avg());
    }
}
