<?php

namespace App\Services\Admin;

use App\Enums\AccountType;
use App\Repositories\AdminDashboardRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class AnalyticsService
{
    private const PAID_STATUS = 'paid';

    public function __construct(
        private readonly AdminDashboardRepository $dashboardRepository,
    ) {}

    public function getOverview(Carbon $now): array
    {
        $thisMonth = $now->copy()->startOfMonth();
        $lastMonth = $now->copy()->subMonth()->startOfMonth();
        $lastMonthEnd = $now->copy()->subMonth()->endOfMonth();

        $months = collect(range(0, 11))->map(
            fn (int $offset) => $now->copy()->subMonths(11 - $offset)->startOfMonth(),
        );
        $reportStart = $months->first()->copy()->startOfMonth();
        $reportEnd = $months->last()->copy()->endOfMonth();

        $shopsThisMonth = $this->dashboardRepository->shopsCountSince($thisMonth);
        $shopsLastMonth = $this->dashboardRepository->shopsCountBetween($lastMonth, $lastMonthEnd);
        $customersThisMonth = $this->dashboardRepository->usersCountByRoleSince(
            AccountType::Customer->value,
            $thisMonth,
        );
        $customersLastMonth = $this->dashboardRepository->usersCountByRoleBetween(
            AccountType::Customer->value,
            $lastMonth,
            $lastMonthEnd,
        );
        $revenueThisMonth = $this->dashboardRepository->revenueBetween($thisMonth, $now);
        $revenueLastMonth = $this->dashboardRepository->revenueBetween($lastMonth, $lastMonthEnd);
        $ordersThisMonth = $this->dashboardRepository->ordersCountByStatusSince(
            self::PAID_STATUS,
            $thisMonth,
        );
        $ordersLastMonth = $this->dashboardRepository->ordersCountByStatusBetween(
            self::PAID_STATUS,
            $lastMonth,
            $lastMonthEnd,
        );

        return [
            'stats' => [
                'total_shops' => [
                    'value' => $this->dashboardRepository->shopsTotalCount(),
                    'change' => $this->percentageChange($shopsThisMonth, $shopsLastMonth),
                ],
                'total_customers' => [
                    'value' => $this->dashboardRepository->usersCountByRole(AccountType::Customer->value),
                    'change' => $this->percentageChange($customersThisMonth, $customersLastMonth),
                ],
                'total_revenue' => [
                    'value' => $this->dashboardRepository->totalRevenue(),
                    'change' => $this->percentageChange($revenueThisMonth, $revenueLastMonth),
                ],
                'total_orders' => [
                    'value' => $this->dashboardRepository->ordersCountByStatus(self::PAID_STATUS),
                    'change' => $this->percentageChange($ordersThisMonth, $ordersLastMonth),
                ],
            ],
            'revenue_chart' => $this->buildChart(
                $months,
                $this->dashboardRepository->revenueByMonthBetween($reportStart, $reportEnd),
                'amount',
                fn (mixed $value): float => (float) $value,
            ),
            'orders_chart' => $this->buildChart(
                $months,
                $this->dashboardRepository->ordersByMonthBetween(
                    self::PAID_STATUS,
                    $reportStart,
                    $reportEnd,
                ),
                'count',
                fn (mixed $value): int => (int) $value,
            ),
            'registrations_chart' => $this->buildChart(
                $months,
                $this->dashboardRepository->usersByMonthForRoleBetween(
                    AccountType::Customer->value,
                    $reportStart,
                    $reportEnd,
                ),
                'count',
                fn (mixed $value): int => (int) $value,
            ),
            'top_shops' => $this->formatTopShops(
                $this->dashboardRepository->getTopPerformingShops(
                    $thisMonth,
                    $lastMonth,
                    $lastMonthEnd,
                ),
            ),
        ];
    }

    private function buildChart(
        Collection $months,
        Collection $values,
        string $valueKey,
        callable $cast,
    ): Collection {
        return $months->map(fn (Carbon $month) => [
            'month' => $month->format('M'),
            $valueKey => $cast($values[$month->format('Y-m')] ?? 0),
        ]);
    }

    private function formatTopShops(Collection $shops): Collection
    {
        return $shops->map(function (object $shop, int $index): array {
            $revenueThisMonth = (float) $shop->revenue_this_month;
            $revenueLastMonth = (float) $shop->revenue_last_month;

            return [
                'rank' => $index + 1,
                'name' => $shop->shop_name,
                'location' => "{$shop->barangay}, {$shop->municipality}",
                'revenue' => (float) $shop->revenue,
                'orders' => (int) $shop->orders,
                'growth' => $this->percentageChange($revenueThisMonth, $revenueLastMonth),
            ];
        })->values();
    }

    private function percentageChange(float|int $current, float|int $previous): float|int
    {
        if ($previous > 0) {
            return round((($current - $previous) / $previous) * 100, 1);
        }

        return $current > 0 ? 100 : 0;
    }
}
