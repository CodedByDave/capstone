<?php

namespace App\Services;

use App\Repositories\ShopAnalyticsRepository;
use App\Repositories\ShopDashboardRepository;
use Illuminate\Support\Carbon;

/**
 * Decision Support: rule-based insights drawn from a shop's operational, inventory,
 * financial and HR data. Every insight names the rule that produced it.
 */
class ShopInsightService
{
    public const MODULE_OPERATIONS = 'operations';

    public const MODULE_INVENTORY = 'inventory';

    public const MODULE_FINANCE = 'finance';

    public const MODULE_HRM = 'hrm';

    private const TONE_RANK = ['urgent' => 0, 'heads_up' => 1, 'tip' => 2];

    private const WEEKDAYS = [1 => 'Sundays', 2 => 'Mondays', 3 => 'Tuesdays', 4 => 'Wednesdays', 5 => 'Thursdays', 6 => 'Fridays', 7 => 'Saturdays'];

    private const CANCELLATION_RATE_THRESHOLD = 15;

    private const EXPENSE_GROWTH_THRESHOLD = 25;

    private const REVENUE_DROP_THRESHOLD = 20;

    private const ABSENCE_RATE_THRESHOLD = 20;

    private const PAYROLL_SHARE_THRESHOLD = 50;

    private const SERVICE_CONCENTRATION_THRESHOLD = 60;

    public function __construct(
        private readonly ShopAnalyticsRepository $analytics,
        private readonly ShopDashboardRepository $dashboard,
        private readonly StockReorderService $reorder,
        private readonly StaffingForecastService $staffing,
        private readonly ServicePricingService $pricing,
    ) {}

    /**
     * Insights ranked most urgent first. Always returns at least one entry.
     *
     * @return list<array{key: string, tone: string, module: string, title: string, message: string, rule: string, href: ?string, action: ?string}>
     */
    public function forShop(int $shopId, ?Carbon $now = null): array
    {
        $now ??= Carbon::now();

        $insights = collect([
            ...$this->operations($shopId, $now),
            ...$this->inventory($shopId, $now),
            ...$this->finance($shopId, $now),
            ...$this->hrm($shopId, $now),
        ])->sortBy(fn (array $i) => self::TONE_RANK[$i['tone']])->values()->all();

        return $insights ?: [$this->insight(
            'clear', 'ok', self::MODULE_OPERATIONS,
            'Nothing needs attention',
            'No overdue orders, stock issues, spending spikes or attendance problems right now.',
            'all decision support rules passed',
        )];
    }

    private function operations(int $shopId, Carbon $now): array
    {
        $insights = [];
        $overdue = $this->dashboard->overdueOrdersCount($shopId, $now);
        $open = $this->dashboard->openOrdersCount($shopId);

        if ($overdue > 0) {
            $insights[] = $this->insight(
                'overdue', 'urgent', self::MODULE_OPERATIONS,
                "{$overdue} ".($overdue === 1 ? 'order is' : 'orders are').' overdue',
                "{$overdue} of {$open} open orders are past their due time. Prioritise them or tell the customers.",
                'open order past due time',
                '/shop/operations/orders', 'View orders',
            );
        }

        $unpaid = $this->dashboard->unpaidBalance($shopId);
        if ($unpaid['amount'] > 0) {
            $insights[] = $this->insight(
                'unpaid', 'heads_up', self::MODULE_FINANCE,
                '₱'.number_format($unpaid['amount'], 2).' not yet collected',
                "{$unpaid['orders']} ".($unpaid['orders'] === 1 ? 'order still has' : 'orders still have').' a balance. Follow up before releasing more orders on credit.',
                'order with balance due',
                '/shop/operations/orders?payment_status=unpaid', 'View unpaid',
            );
        }

        $since = $now->copy()->subDays(30);
        $statuses = $this->analytics->orderStatusCounts($shopId, $since, $now);
        $total = $statuses->sum();
        $cancelled = (int) ($statuses['cancelled'] ?? 0);
        if ($total >= 10 && ($rate = (int) round($cancelled / $total * 100)) >= self::CANCELLATION_RATE_THRESHOLD) {
            $insights[] = $this->insight(
                'cancellations', 'heads_up', self::MODULE_OPERATIONS,
                "{$rate}% of recent orders were cancelled",
                "{$cancelled} of {$total} orders in the last 30 days were cancelled. Check complaints and turnaround times for the cause.",
                'cancelled orders ≥ '.self::CANCELLATION_RATE_THRESHOLD.'% of last 30 days',
                '/shop/operations/complaints', 'View complaints',
            );
        }

        $services = $this->analytics->servicePerformance($shopId, $since, $now);
        $revenue = $services->sum('revenue');
        $top = $services->first();
        if ($services->count() > 1 && $revenue > 0 && ($share = (int) round($top['revenue'] / $revenue * 100)) >= self::SERVICE_CONCENTRATION_THRESHOLD) {
            $insights[] = $this->insight(
                'service-concentration', 'tip', self::MODULE_OPERATIONS,
                "{$top['name']} brings in {$share}% of sales",
                'Your income depends heavily on one service. Promote your other services to spread the risk.',
                'one service ≥ '.self::SERVICE_CONCENTRATION_THRESHOLD.'% of last 30 days sales',
                '/shop/operations/promos', 'Create promotion',
            );
        }

        $flagged = collect($this->pricing->analyze($shopId, $since, $now));
        $belowCost = $flagged->where('status', ServicePricingService::BELOW_COST);
        $lowMargin = $flagged->where('status', ServicePricingService::LOW_MARGIN);
        if ($belowCost->isNotEmpty()) {
            $first = $belowCost->first();
            $insights[] = $this->insight(
                'priced-below-cost', 'urgent', self::MODULE_FINANCE,
                $belowCost->count() === 1 ? "{$first['name']} is priced below its supply cost" : "{$belowCost->count()} services are priced below supply cost",
                "{$first['name']} sells at ₱".number_format($first['price'], 2)." {$first['pricing_unit']} but its supplies cost ₱".number_format($first['supply_cost'], 2).'. A price of ₱'.number_format((float) $first['suggested_price'], 2).' would reach a '.ServicePricingService::TARGET_MARGIN_PERCENT.'% margin.',
                'service price < supply cost per order',
                '/shop/reports/pricing', 'Review pricing',
            );
        } elseif ($lowMargin->isNotEmpty()) {
            $first = $lowMargin->first();
            $insights[] = $this->insight(
                'low-margin', 'heads_up', self::MODULE_FINANCE,
                $lowMargin->count() === 1 ? "{$first['name']} has a thin margin" : "{$lowMargin->count()} services have thin margins",
                "{$first['name']} keeps only {$first['margin_percent']}% after supplies. A price of ₱".number_format((float) $first['suggested_price'], 2).' would reach '.ServicePricingService::TARGET_MARGIN_PERCENT.'%.',
                'margin after supplies < '.ServicePricingService::LOW_MARGIN_PERCENT.'%',
                '/shop/reports/pricing', 'Review pricing',
            );
        }

        $byDay = $this->analytics->ordersByWeekday($shopId, $now->copy()->subWeeks(8), $now);
        $orders = $byDay->sum();
        if ($orders >= 10) {
            $dow = $byDay->sortDesc()->keys()->first();
            $share = (int) round($byDay[$dow] / $orders * 100);
            $insights[] = $this->insight(
                'busiest', 'tip', self::MODULE_OPERATIONS,
                self::WEEKDAYS[$dow].' are your busiest',
                "{$share}% of orders in the last 8 weeks came on ".self::WEEKDAYS[$dow].'. Plan staff and supplies around it.',
                'orders grouped by day of week',
                '/shop/employee', 'View employees',
            );
        }

        return $insights;
    }

    private function inventory(int $shopId, Carbon $now): array
    {
        $insights = [];
        $out = $this->analytics->outOfStockCount($shopId);
        $low = $this->analytics->lowStockCount($shopId);

        if ($out + $low > 0) {
            $insights[] = $this->insight(
                'stock', $out > 0 ? 'urgent' : 'heads_up', self::MODULE_INVENTORY,
                ($out + $low).' '.($out + $low === 1 ? 'supply needs' : 'supplies need').' restocking',
                "{$out} out of stock and {$low} at or below the minimum level.",
                'stock at or below minimum level',
                '/shop/inventory', 'Reorder',
            );
        }

        $runningOut = $this->reorder->forShop($shopId, $now)
            ->filter(fn (array $item) => $item['needs_reorder'] && $item['quantity'] > 0)
            ->sortBy('days_left')
            ->values();

        if ($runningOut->isNotEmpty()) {
            $first = $runningOut->first();
            $others = $runningOut->count() - 1;
            $left = max(1, (int) ceil($first['days_left']));
            $dayWord = $left === 1 ? 'day' : 'days';
            $order = $first['suggested_quantity'] > 0 ? " Suggested order: {$first['suggested_quantity']} {$first['unit']}." : '';
            $more = $others > 0 ? " {$others} more ".($others === 1 ? 'item is' : 'items are').' in the same position.' : '';
            $insights[] = $this->insight(
                'stock-runway', 'heads_up', self::MODULE_INVENTORY,
                "{$first['name']} may run out in about {$left} {$dayWord}",
                "At the last 30 days' usage, {$first['quantity']} {$first['unit']} lasts about {$left} {$dayWord} and the supplier takes {$first['lead_time_days']}.{$order}{$more}",
                'stock ÷ daily use ≤ supplier lead time + '.StockReorderService::SAFETY_DAYS.' days',
                '/shop/inventory', 'Plan restock',
            );
        }

        return $insights;
    }

    private function finance(int $shopId, Carbon $now): array
    {
        $insights = [];
        $monthStart = $now->copy()->startOfMonth();
        $revenue = $this->analytics->revenueBetween($shopId, $monthStart, $now);
        $expenses = $this->analytics->expensesBetween($shopId, $monthStart, $now);

        if ($expenses > 0 && $expenses > $revenue) {
            $gap = number_format($expenses - $revenue, 2);
            $insights[] = $this->insight(
                'loss', 'urgent', self::MODULE_FINANCE,
                'Spending is above income this month',
                "Expenses are ₱{$gap} more than collected sales so far this month. Review the largest expense categories.",
                'month-to-date expenses > month-to-date sales',
                '/shop/finance', 'View finance',
            );
        }

        $elapsed = max(1, (int) $monthStart->diffInDays($now) + 1);
        $prevEnd = $monthStart->copy()->subSecond();
        $prevStart = $monthStart->copy()->subDays($elapsed);
        $prevExpenses = $this->analytics->expensesBetween($shopId, $prevStart, $prevEnd);
        $prevRevenue = $this->analytics->revenueBetween($shopId, $prevStart, $prevEnd);

        if ($prevExpenses > 0 && ($growth = (int) round(($expenses - $prevExpenses) / $prevExpenses * 100)) >= self::EXPENSE_GROWTH_THRESHOLD) {
            $insights[] = $this->insight(
                'expense-growth', 'heads_up', self::MODULE_FINANCE,
                "Expenses are up {$growth}%",
                'Spending this month is well above the same stretch of the previous period.',
                'expenses ≥ +'.self::EXPENSE_GROWTH_THRESHOLD.'% vs previous equal period',
                '/shop/finance/expenses', 'View expenses',
            );
        }

        if ($prevRevenue > 0 && ($drop = (int) round(($prevRevenue - $revenue) / $prevRevenue * 100)) >= self::REVENUE_DROP_THRESHOLD) {
            $insights[] = $this->insight(
                'revenue-drop', 'heads_up', self::MODULE_FINANCE,
                "Sales are down {$drop}%",
                'Collected sales are well below the same stretch of the previous period. Consider a promotion or following up on unpaid orders.',
                'sales ≤ −'.self::REVENUE_DROP_THRESHOLD.'% vs previous equal period',
                '/shop/operations/promos', 'Create promotion',
            );
        }

        return $insights;
    }

    private function hrm(int $shopId, Carbon $now): array
    {
        $insights = [];
        $since = $now->copy()->subDays(30);
        $attendance = $this->analytics->attendanceCounts($shopId, $since);
        $records = $attendance->sum();

        if ($records >= 10) {
            $bad = (int) ($attendance['absent'] ?? 0) + (int) ($attendance['late'] ?? 0);
            $rate = (int) round($bad / $records * 100);
            if ($rate >= self::ABSENCE_RATE_THRESHOLD) {
                $insights[] = $this->insight(
                    'attendance', 'heads_up', self::MODULE_HRM,
                    "{$rate}% of attendance records are late or absent",
                    "{$bad} of {$records} records in the last 30 days. Review schedules and talk to affected employees.",
                    'late + absent ≥ '.self::ABSENCE_RATE_THRESHOLD.'% of last 30 days',
                    '/shop/attendance', 'View attendance',
                );
            }
        }

        $insights = [...$insights, ...$this->staffingInsights($shopId, $now)];

        $payroll = $this->analytics->payrollCostBetween($shopId, $since, $now);
        $revenue = $this->analytics->revenueBetween($shopId, $since, $now);
        if ($payroll > 0 && $revenue > 0 && ($share = (int) round($payroll / $revenue * 100)) >= self::PAYROLL_SHARE_THRESHOLD) {
            $insights[] = $this->insight(
                'payroll-share', 'heads_up', self::MODULE_HRM,
                "Payroll takes {$share}% of sales",
                'Labor cost is high compared with income over the last 30 days. Check staffing against order volume.',
                'payroll ≥ '.self::PAYROLL_SHARE_THRESHOLD.'% of last 30 days sales',
                '/shop/payroll', 'View payroll',
            );
        }

        return $insights;
    }

    private function staffingInsights(int $shopId, Carbon $now): array
    {
        $forecast = collect($this->staffing->forShop($shopId, $now));
        $insights = [];

        $short = $forecast->where('status', StaffingForecastService::UNDERSTAFFED);
        if ($short->isNotEmpty()) {
            $days = $short->map(fn (array $d) => "{$d['day']} ({$d['expected_orders']} orders, {$d['scheduled_staff']} of {$d['required_staff']} staff)")->implode('; ');
            $insights[] = $this->insight(
                'understaffed', 'heads_up', self::MODULE_HRM,
                'Understaffed on '.$short->pluck('day')->implode(', '),
                "Expected orders for the next 7 days need more staff than are scheduled: {$days}. Add shifts or move days off.",
                'forecast orders ÷ '.StaffingForecastService::ORDERS_PER_STAFF.' per employee > scheduled employees',
                '/shop/employee', 'Edit schedules',
            );
        }

        $surplus = $forecast->where('status', StaffingForecastService::OVERSTAFFED);
        if ($surplus->isNotEmpty()) {
            $insights[] = $this->insight(
                'overstaffed', 'tip', self::MODULE_HRM,
                'More staff than needed on '.$surplus->pluck('day')->implode(', '),
                'Forecast demand is low for these days. Consider moving a shift to a busier day.',
                'scheduled employees > forecast need + '.StaffingForecastService::SURPLUS_TOLERANCE,
                '/shop/employee', 'Edit schedules',
            );
        }

        return $insights;
    }

    private function insight(string $key, string $tone, string $module, string $title, string $message, string $rule, ?string $href = null, ?string $action = null): array
    {
        return compact('key', 'tone', 'module', 'title', 'message', 'rule', 'href', 'action');
    }
}
