<?php

namespace App\Services;

use App\Models\Shop;
use App\Models\ShopOrder;

/**
 * The weight range one rider run can carry, as set by the shop owner. Each run carries one order,
 * so an order is checked as a whole: the main service plus any extra weighed services. Orders
 * priced per piece have no weight and are never held back. Both ends of the range are allowed.
 */
class RiderLoadLimitService
{
    public const PURPOSE_PICKUP = 'pickup';

    public const PURPOSE_DELIVERY = 'delivery';

    /** @return array{min: float|null, max: float|null} */
    public function limitsFor(?Shop $shop): array
    {
        return [
            'min' => $shop?->rider_min_weight_kg !== null ? (float) $shop->rider_min_weight_kg : null,
            'max' => $shop?->rider_max_weight_kg !== null ? (float) $shop->rider_max_weight_kg : null,
        ];
    }

    /**
     * The order's laundry weight in kg: the actual weight once weighed, otherwise the estimate.
     * Null when nothing on the order is weighed (per-piece only).
     */
    public function orderWeight(ShopOrder $order): ?float
    {
        $order->loadMissing('serviceLines');

        return $this->weightFromParts(
            (string) $order->pricing_model,
            $order->actual_weight_kg !== null ? (float) $order->actual_weight_kg : null,
            $order->estimated_weight_kg !== null ? (float) $order->estimated_weight_kg : null,
            $order->serviceLines->map(fn ($line) => [
                'pricing_model' => $line->pricing_model,
                'weight_kg' => $line->weight_kg,
            ])->all(),
        );
    }

    /**
     * The weight of an order that is still being created.
     *
     * @param  array<int, array{pricing_model?: string|null, weight_kg?: mixed}>  $lines
     */
    public function weightFromParts(string $pricingModel, ?float $actual, ?float $estimated, array $lines = []): ?float
    {
        $total = 0.0;
        $weighed = false;

        if ($pricingModel !== 'per_piece' && ($actual ?? $estimated) !== null) {
            $total += (float) ($actual ?? $estimated);
            $weighed = true;
        }

        foreach ($lines as $line) {
            if (($line['pricing_model'] ?? null) !== 'per_piece' && filled($line['weight_kg'] ?? null)) {
                $total += (float) $line['weight_kg'];
                $weighed = true;
            }
        }

        return $weighed ? round($total, 2) : null;
    }

    /** Whether a rider run may carry this weight. An unknown weight or a shop without limits always may. */
    public function allows(?Shop $shop, ?float $weight): bool
    {
        return $this->violation($shop, $weight, self::PURPOSE_DELIVERY) === null;
    }

    /** Why a rider run cannot carry this weight, worded for the person booking, or null when it can. */
    public function violation(?Shop $shop, ?float $weight, string $purpose): ?string
    {
        if ($weight === null) {
            return null;
        }

        ['min' => $min, 'max' => $max] = $this->limitsFor($shop);
        $leg = $purpose === self::PURPOSE_PICKUP ? 'pickup' : 'delivery';

        if ($max !== null && $weight > $max) {
            return $purpose === self::PURPOSE_PICKUP
                ? 'A rider can carry up to '.$this->kg($max).' per pickup. This laundry is '.$this->kg($weight).', so please drop it off at the shop instead.'
                : 'A rider can carry up to '.$this->kg($max).' per delivery. This laundry is '.$this->kg($weight).', so it cannot go out with a rider.';
        }

        if ($min !== null && $weight < $min) {
            return 'Rider '.$leg.' needs at least '.$this->kg($min).' of laundry. This order is '.$this->kg($weight).'.';
        }

        return null;
    }

    /** "Over rider limit (18 kg / max 15 kg)" for the Logistics board, or null when the run is within the limit. */
    public function boardLabel(?Shop $shop, ?float $weight): ?string
    {
        if ($weight === null) {
            return null;
        }

        ['min' => $min, 'max' => $max] = $this->limitsFor($shop);

        return match (true) {
            $max !== null && $weight > $max => 'Over rider limit ('.$this->kg($weight).' / max '.$this->kg($max).')',
            $min !== null && $weight < $min => 'Under rider minimum ('.$this->kg($weight).' / min '.$this->kg($min).')',
            default => null,
        };
    }

    private function kg(float $value): string
    {
        return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.').' kg';
    }
}
