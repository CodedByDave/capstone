<?php

namespace App\Services;

use App\Models\ShopService as LaundryService;
use App\Repositories\ShopAnalyticsRepository;
use Illuminate\Support\Carbon;

/** Compares each service's price with the cost of the supplies it uses. */
class ServicePricingService
{
    /** Margin (% of price) under which a service is flagged. */
    public const LOW_MARGIN_PERCENT = 30;

    /** Margin (% of price) the suggested price aims for. */
    public const TARGET_MARGIN_PERCENT = 40;

    public const BELOW_COST = 'below_cost';

    public const LOW_MARGIN = 'low_margin';

    public const HEALTHY = 'healthy';

    public const NO_SUPPLIES = 'no_supplies';

    private const UNIT_LABELS = ['per_kg' => 'per kg', 'per_bundle' => 'per bundle', 'per_piece' => 'per piece'];

    public function __construct(private readonly ShopAnalyticsRepository $analytics) {}

    /**
     * Active services with price, supply cost per pricing unit, margin and order volume in the range.
     * Only supply cost counts; labor and overhead are not included.
     *
     * @return list<array<string, mixed>> Worst margin first, services without a supply list last.
     */
    public function analyze(int $shopId, Carbon $from, Carbon $to): array
    {
        $orders = $this->analytics->orderCountsByService($shopId, $from, $to);

        return $this->analytics->activeServicesWithSupplies($shopId)
            ->map(fn (LaundryService $service) => $this->row($service, (int) ($orders[$service->id] ?? 0)))
            ->sortBy(fn (array $row) => $row['status'] === self::NO_SUPPLIES ? PHP_INT_MAX : ($row['margin_percent'] ?? PHP_INT_MAX))
            ->values()
            ->all();
    }

    private function row(LaundryService $service, int $orders): array
    {
        $price = (float) match ($service->pricing_model) {
            'per_bundle' => $service->bundle_price,
            'per_piece' => $service->price_per_piece,
            default => $service->price_per_kg,
        };

        $supplies = $service->supplies;
        $cost = round($supplies->sum(fn ($supply) => (float) $supply->pivot->quantity * (float) $supply->unit_price), 2);
        $margin = round($price - $cost, 2);
        $percent = $price > 0 ? round($margin / $price * 100, 1) : null;

        $status = match (true) {
            $supplies->isEmpty() => self::NO_SUPPLIES,
            $margin < 0 => self::BELOW_COST,
            $percent !== null && $percent < self::LOW_MARGIN_PERCENT => self::LOW_MARGIN,
            default => self::HEALTHY,
        };

        return [
            'id' => $service->id,
            'name' => $service->service_name,
            'pricing_unit' => self::UNIT_LABELS[$service->pricing_model] ?? 'per kg',
            'price' => $price,
            'supply_cost' => $cost,
            'cost_complete' => $supplies->every(fn ($supply) => $supply->unit_price !== null),
            'margin' => $margin,
            'margin_percent' => $percent,
            'orders' => $orders,
            'status' => $status,
            'suggested_price' => $cost > 0 ? (float) ceil($cost / (1 - self::TARGET_MARGIN_PERCENT / 100)) : null,
        ];
    }
}
