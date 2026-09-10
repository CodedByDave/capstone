<?php

namespace App\Services\Admin;

use App\Models\User;
use App\Repositories\AdminDashboardRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class AdminDashboardService
{
    public function __construct(
        private readonly AdminDashboardRepository $adminDashboardRepository,
    ) {}

    // Shop related information

    public function getRecentShops(Carbon $now): Collection
    {
        return $this->adminDashboardRepository->getRecentShops($now);
    }

    public function shopsTotalCount(): int
    {
        return $this->adminDashboardRepository->shopsTotalCount();
    }

    public function shopsCountSince(Carbon $date): int
    {
        return $this->adminDashboardRepository->shopsCountSince($date);
    }

    public function shopsCountBetween(Carbon $start, Carbon $end): int
    {
        return $this->adminDashboardRepository->shopsCountBetween(
            $start,
            $end
        );
    }

    public function shopsCountByStatus(string $status): int
    {
        return $this->adminDashboardRepository->shopsCountByStatus($status);
    }

    // User related information

    public function usersCountByRole(string $role): int
    {
        return $this->adminDashboardRepository->usersCountByRole($role);
    }

    public function usersCountSince(Carbon $date): int
    {
        return $this->adminDashboardRepository->usersCountSince($date);
    }

    public function usersCountBetween(Carbon $start, Carbon $end): int
    {
        return $this->adminDashboardRepository->usersCountBetween(
            $start,
            $end
        );
    }

    // Order and subscription related information

    public function ordersTotalCount(): int
    {
        return $this->adminDashboardRepository->ordersTotalCount();
    }

    public function activeSubscriptionsCount(Carbon $now): int
    {
        return $this->adminDashboardRepository->activeSubcriptionsCount($now);
    }

    public function expiringSubscriptionsInSevenDays(
        Carbon $now
    ): Collection {
        return $this->adminDashboardRepository
            ->expiringSubscriptionsInSevenDays($now);
    }

    public function expiredSubscriptionsCount(Carbon $now): int
    {
        return $this->adminDashboardRepository
            ->expiredSubscriptionsCount($now);
    }

    public function ordersCountSince(Carbon $date): int
    {
        return $this->adminDashboardRepository->ordersCountSince($date);
    }

    public function ordersCountBetween(
        Carbon $start,
        Carbon $end
    ): int {
        return $this->adminDashboardRepository->ordersCountBetween(
            $start,
            $end
        );
    }

    public function getPlanBreakdown(Carbon $now): Collection
    {
        return $this->adminDashboardRepository->getPlanBreakdown($now);
    }

    // Revenue information

    public function getMonthlyRevenue(Carbon $now): Collection
    {
        return $this->adminDashboardRepository->getMonthlyRevenue($now);
    }

    public function getMonthlyOrders(Carbon $now): Collection
    {
        return $this->adminDashboardRepository->getMonthlyOrders($now);
    }

    // Dashboard alerts

    public function getOverdueSubscriptions(Carbon $now): Collection
    {
        return $this->adminDashboardRepository->getOverdueSubscriptions(
            $now
        );
    }

    public function getInactiveShops(Carbon $now): Collection
    {
        return $this->adminDashboardRepository->getInactiveShops($now);
    }

    // Dashboard summary

    public function getDashboardData(Carbon $now): array
    {
        $thisMonth = $now->copy()->startOfMonth();

        $lastMonth = $now
            ->copy()
            ->subMonth()
            ->startOfMonth();

        $lastMonthEnd = $now
            ->copy()
            ->subMonth()
            ->endOfMonth();

        // Shop statistics

        $shopsThisMonth = $this->shopsCountSince($thisMonth);

        $shopsLastMonth = $this->shopsCountBetween(
            $lastMonth,
            $lastMonthEnd
        );

        // User statistics

        $newUsersMonth = $this->usersCountSince($thisMonth);

        $newUsersLastMonth = $this->usersCountBetween(
            $lastMonth,
            $lastMonthEnd
        );

        // Order statistics

        $ordersThisMonth = $this->ordersCountSince($thisMonth);

        $ordersLastMonth = $this->ordersCountBetween(
            $lastMonth,
            $lastMonthEnd
        );

        // Revenue statistics

        $revenueThisMonth = $this->getRevenueBetween(
            $thisMonth,
            $now
        );

        $revenueLastMonth = $this->getRevenueBetween(
            $lastMonth,
            $lastMonthEnd
        );

        // Chart data

        $months = collect(range(1, 12));

        $monthlyRevenue = $this->getMonthlyRevenue($now);

        $revenueChart = $months
            ->map(fn ($month) => (float) (
                $monthlyRevenue[$month] ?? 0
            ))
            ->values();

        $monthlyOrders = $this->getMonthlyOrders($now);

        $ordersChart = $months
            ->map(fn ($month) => (int) (
                $monthlyOrders[$month] ?? 0
            ))
            ->values();

        $monthlyShops = $this->adminDashboardRepository
            ->getMonthlyShopRegistrations($now);

        $shopsChart = $months
            ->map(fn ($month) => (int) (
                $monthlyShops[$month] ?? 0
            ))
            ->values();

        // Alerts

        $expiringSubscriptions =
            $this->expiringSubscriptionsInSevenDays($now);

        $overdueSubscriptions =
            $this->getOverdueSubscriptions($now);

        $inactiveShops =
            $this->getInactiveShops($now);

        return [
            'kpis' => [
                'totalShops' => $this->shopsTotalCount(),

                'activeShops' => $this->shopsCountByStatus(
                    'active'
                ),

                'disabledShops' => $this->shopsCountByStatus(
                    'disabled'
                ),

                'pendingShops' => $this->shopsCountByStatus(
                    'pending'
                ),

                'shopChange' => $this->percentageChange(
                    $shopsThisMonth,
                    $shopsLastMonth
                ),

                'totalOwners' => $this->usersCountByRole(
                    User::ROLE_OWNER
                ),

                'totalStaff' =>
                    $this->usersCountByRole(User::ROLE_STAFF)
                    + $this->usersCountByRole(User::ROLE_MANAGER),

                'totalCustomers' => $this->usersCountByRole(
                    User::ROLE_USER
                ),

                'newUsersMonth' => $newUsersMonth,

                'usersChange' => $this->percentageChange(
                    $newUsersMonth,
                    $newUsersLastMonth
                ),

                'totalOrders' => $this->ordersTotalCount(),

                'activeSubscriptions' =>
                    $this->activeSubscriptionsCount($now),

                'expiredOrders' =>
                    $this->expiredSubscriptionsCount($now),

                'ordersThisMonth' => $ordersThisMonth,

                'ordersChange' => $this->percentageChange(
                    $ordersThisMonth,
                    $ordersLastMonth
                ),

                'revenueThisMonth' => $revenueThisMonth,

                'revenueLastMonth' => $revenueLastMonth,

                'revenueChange' => $this->percentageChange(
                    $revenueThisMonth,
                    $revenueLastMonth
                ),

                'totalRevenue' => $this->getTotalRevenue(),

                'totalEmployees' => $this->adminDashboardRepository
                    ->employeesTotalCount(),

                'activeEmployees' => $this->adminDashboardRepository
                    ->employeesCountByStatus('Active'),

                'totalInventory' => $this->adminDashboardRepository
                    ->inventoryTotalCount(),

                'lowStockCount' => $this->adminDashboardRepository
                    ->inventoryLowStockCount(),

                'outOfStockCount' => $this->adminDashboardRepository
                    ->inventoryOutOfStockCount(),
            ],

            'alerts' => [
                'overduePayments' =>
                    $overdueSubscriptions
                        ->map(
                            fn ($order) =>
                                $order->shop_name
                                ?? $order->user?->name
                        )
                        ->values(),

                'expiringShops' =>
                    $expiringSubscriptions
                        ->map(
                            fn ($order) =>
                                $order->shop_name
                                ?? $order->user?->name
                        )
                        ->values(),

                'inactiveShops' =>
                    $inactiveShops
                        ->map(
                            fn ($shop) =>
                                $shop->shop_name
                        )
                        ->values(),

                'pendingShops' =>
                    $this->shopsCountByStatus('pending'),

                'lowStockCount' => $this->adminDashboardRepository
                    ->inventoryLowStockCount(),

                'outOfStockCount' => $this->adminDashboardRepository
                    ->inventoryOutOfStockCount(),
            ],

            'charts' => [
                'revenue' => $revenueChart,
                'orders' => $ordersChart,
                'shops' => $shopsChart,
            ],

            'planBreakdown' =>
                $this->getPlanBreakdown($now),

            'shops' =>
                $this->getRecentShops($now),
        ];
    }

    // Revenue calculations

    private function getRevenueBetween(
        Carbon $start,
        Carbon $end
    ): float {
        return $this->adminDashboardRepository
            ->revenueBetween($start, $end);
    }

    private function getTotalRevenue(): float
    {
        return $this->adminDashboardRepository
            ->totalRevenue();
    }

    // Percentage calculation

    private function percentageChange(
        float|int $current,
        float|int $previous
    ): float|int {
        if ($previous <= 0) {
            return 0;
        }

        return round(
            (($current - $previous) / $previous) * 100,
            1
        );
    }
}
