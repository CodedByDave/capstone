<?php

namespace App\Services;

use App\Repositories\ShopAnalyticsRepository;
use Illuminate\Support\Carbon;

/** Finance and operations report figures for one shop over a chosen period. */
class ShopReportService
{
    public const DEFAULT_PERIOD = 'month';

    public const PERIODS = ['today', 'week', 'month', 'year'];

    private const WEEKDAYS = [1 => 'Sun', 2 => 'Mon', 3 => 'Tue', 4 => 'Wed', 5 => 'Thu', 6 => 'Fri', 7 => 'Sat'];

    public function __construct(
        private readonly ShopAnalyticsRepository $analytics,
        private readonly ServicePricingService $pricing,
    ) {}

    public function finance(int $shopId, string $period): array
    {
        [$from, $to] = $this->range($period);

        $revenue = $this->analytics->revenueBetween($shopId, $from, $to);
        $expenses = $this->analytics->expensesBetween($shopId, $from, $to);
        $net = $revenue - $expenses;
        $categories = $this->analytics->expensesByCategory($shopId, $from, $to);

        return [
            'period' => $period,
            'stats' => [
                'revenue' => $revenue,
                'expenses' => $expenses,
                'net' => $net,
                'margin' => $revenue > 0 ? round($net / $revenue * 100, 1) : null,
            ],
            'monthlyTrend' => $this->monthlyTrend($shopId),
            'expenseCategories' => $categories->map(fn (array $c) => $c + [
                'share' => $expenses > 0 ? round($c['total'] / $expenses * 100, 1) : 0.0,
            ])->values()->all(),
        ];
    }

    public function operations(int $shopId, string $period): array
    {
        [$from, $to] = $this->range($period);

        $statuses = $this->analytics->orderStatusCounts($shopId, $from, $to);
        $total = (int) $statuses->sum();
        $finished = (int) ($statuses['completed'] ?? 0) + (int) ($statuses['picked_up'] ?? 0);
        $cancelled = (int) ($statuses['cancelled'] ?? 0);
        $byDay = $this->analytics->ordersByWeekday($shopId, $from, $to);

        return [
            'period' => $period,
            'stats' => [
                'total' => $total,
                'completed' => $finished,
                'cancelled' => $cancelled,
                'completion_rate' => $total > 0 ? round($finished / $total * 100, 1) : null,
                'cancellation_rate' => $total > 0 ? round($cancelled / $total * 100, 1) : null,
                'avg_turnaround_hours' => $this->analytics->averageTurnaroundHours($shopId, $from, $to),
            ],
            'statusBreakdown' => $statuses->map(fn (int $count, string $status) => ['status' => $status, 'count' => $count])->values()->all(),
            'services' => $this->analytics->servicePerformance($shopId, $from, $to)->values()->all(),
            'weekdayDistribution' => collect(self::WEEKDAYS)
                ->map(fn (string $label, int $dow) => ['day' => $label, 'count' => (int) ($byDay[$dow] ?? 0)])
                ->values()
                ->all(),
        ];
    }

    public function pricing(int $shopId, string $period): array
    {
        [$from, $to] = $this->range($period);
        $services = $this->pricing->analyze($shopId, $from, $to);
        $count = fn (string $status) => count(array_filter($services, fn (array $s) => $s['status'] === $status));

        return [
            'period' => $period,
            'targets' => [
                'low_margin_percent' => ServicePricingService::LOW_MARGIN_PERCENT,
                'target_margin_percent' => ServicePricingService::TARGET_MARGIN_PERCENT,
            ],
            'stats' => [
                'services' => count($services),
                'below_cost' => $count(ServicePricingService::BELOW_COST),
                'low_margin' => $count(ServicePricingService::LOW_MARGIN),
                'healthy' => $count(ServicePricingService::HEALTHY),
            ],
            'services' => $services,
        ];
    }

    /** @return array{0: Carbon, 1: Carbon} */
    private function range(string $period): array
    {
        $now = Carbon::now();

        return match ($period) {
            'today' => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
            'week' => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
            'year' => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
            default => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
        };
    }

    /** Revenue against expenses for each of the last six months. */
    private function monthlyTrend(int $shopId): array
    {
        $since = Carbon::now()->subMonths(5)->startOfMonth();
        $revenue = $this->analytics->monthlyRevenue($shopId, $since);
        $expenses = $this->analytics->monthlyExpenses($shopId, $since);

        return collect(range(5, 0))->map(function (int $ago) use ($revenue, $expenses) {
            $month = Carbon::now()->subMonths($ago);

            return [
                'month' => $month->format('M Y'),
                'revenue' => (float) ($revenue[$month->format('Y-m')] ?? 0),
                'expenses' => (float) ($expenses[$month->format('Y-m')] ?? 0),
            ];
        })->all();
    }
}
