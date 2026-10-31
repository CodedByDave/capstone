<?php

namespace App\Services;

use App\Repositories\ShopDashboardRepository;
use Illuminate\Support\Carbon;

class ShopDashboardService
{
    public const PERIOD_TODAY = 'today';

    public const PERIODS = [self::PERIOD_TODAY, '7d', 'month'];

    private const WEEKDAYS = [1 => 'Sundays', 2 => 'Mondays', 3 => 'Tuesdays', 4 => 'Wednesdays', 5 => 'Thursdays', 6 => 'Fridays', 7 => 'Saturdays'];

    public function __construct(
        private readonly ShopDashboardRepository $repository,
        private readonly ShopInsightService $insights,
        private readonly StaffingForecastService $staffing,
    ) {}

    /** Figures for the shop dashboard; revenue and orders cover the chosen period, the rest is current. */
    public function dailySummary(int $shopId, string $period = self::PERIOD_TODAY): array
    {
        $now = Carbon::now();
        $today = $now->copy()->startOfDay();
        [$start, $previousStart, $previousEnd] = $this->periodBounds($period, $today);

        $revenue = $this->repository->revenueBetween($shopId, $start, $now);
        $previousRevenue = $this->repository->revenueBetween($shopId, $previousStart, $previousEnd);
        $orders = $this->repository->ordersCreatedBetween($shopId, $start, $now);
        $previousOrders = $this->repository->ordersCreatedBetween($shopId, $previousStart, $previousEnd);
        $unpaid = $this->repository->unpaidBalance($shopId);

        return [
            'period' => $period,
            'revenue_today' => $revenue,
            'revenue_change' => $previousRevenue > 0
                ? round((($revenue - $previousRevenue) / $previousRevenue) * 100)
                : null,
            'orders_today' => $orders,
            'orders_vs_yesterday' => $orders - $previousOrders,
            'total_orders' => $this->repository->totalOrdersCount($shopId),
            'open_orders' => $this->repository->openOrdersCount($shopId),
            'overdue_orders' => $this->repository->overdueOrdersCount($shopId, $now),
            'unpaid_balance' => $unpaid['amount'],
            'unpaid_orders' => $unpaid['orders'],
            'daily_revenue' => $this->lastSevenDays($shopId, $today),
            'due_today' => $this->dueToday($shopId, $now),
            'busiest_day' => $this->busiestDay($shopId, $now),
            'insights' => $this->insights->forShop($shopId, $now),
            'staffing_forecast' => $this->staffing->forShop($shopId, $now),
        ];
    }

    /** @return array{0: Carbon, 1: Carbon, 2: Carbon} Period start, and the equal-length period right before it. */
    private function periodBounds(string $period, Carbon $today): array
    {
        $start = match ($period) {
            '7d' => $today->copy()->subDays(6),
            'month' => $today->copy()->startOfMonth(),
            default => $today->copy(),
        };
        $days = max(1, (int) $start->diffInDays($today) + 1);

        return [$start, $start->copy()->subDays($days), $start->copy()->subSecond()];
    }

    private function lastSevenDays(int $shopId, Carbon $today): array
    {
        $revenue = $this->repository->dailyRevenue($shopId, $today->copy()->subDays(6));

        return collect(range(6, 0))->map(function (int $ago) use ($today, $revenue) {
            $day = $today->copy()->subDays($ago);

            return [
                'label' => $ago === 0 ? 'Today' : $day->format('D'),
                'revenue' => (float) ($revenue[$day->toDateString()] ?? 0),
            ];
        })->all();
    }

    private function dueToday(int $shopId, Carbon $now): array
    {
        return $this->repository->dueSoon($shopId, $now->copy()->endOfDay())
            ->map(fn ($order) => [
                'order_number' => $order->order_number,
                'customer' => $order->customer_name,
                'service' => $order->service?->service_name,
                'due_at' => $order->estimated_completion_at->format('g:i A'),
                'overdue' => $order->estimated_completion_at->lt($now),
            ])
            ->all();
    }

    /** The weekday with the most orders over the last 8 weeks, or null without enough history. */
    private function busiestDay(int $shopId, Carbon $now): ?array
    {
        $byDay = $this->repository->ordersByWeekday($shopId, $now->copy()->subWeeks(8));
        $total = $byDay->sum();

        if ($total < 10) {
            return null;
        }

        $dow = $byDay->sortDesc()->keys()->first();

        return [
            'day' => self::WEEKDAYS[$dow],
            'share' => (int) round(($byDay[$dow] / $total) * 100),
        ];
    }
}
