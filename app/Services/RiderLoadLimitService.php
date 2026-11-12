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
     * The vehicle in use, the choices, and the load capacity of each choice.
     *
     * @return array{selected: string, choices: list<string>, limits: list<array{type: string, min: float|null, max: float|null}>}
     */
    public function vehicleOptionsFor(Shop $shop): array
    {
        $selected = $shop->rider_vehicle_type ?: Shop::DEFAULT_RIDER_VEHICLE_TYPE;
        $choices = $shop->rider_vehicle_types ?: [Shop::DEFAULT_RIDER_VEHICLE_TYPE];
        $stored = $shop->rider_vehicle_limits ?? [];

        return [
            'selected' => $selected,
            'choices' => $choices,
            'limits' => array_map(function (string $type) use ($shop, $selected, $stored) {
                // The vehicle in use always reports the limit that is enforced.
                $limit = $type === $selected ? $this->limitsFor($shop) : $this->cleanLimit($stored[$type] ?? []);

                return ['type' => $type] + $limit;
            }, $choices),
        ];
    }

    /**
     * The capacity of every vehicle type after a save, keyed by type. Types no longer offered are
     * dropped. Given capacities replace the stored ones; when none are given, the min/max apply to
     * the vehicle in use, as a weights-only save always did.
     *
     * @param  list<string>  $types
     * @param  list<array{type: string, min?: mixed, max?: mixed}>|null  $given
     * @return array<string, array{min: float|null, max: float|null}>
     */
    public function vehicleLimitsAfterSave(Shop $shop, array $types, string $selected, ?array $given, ?float $min, ?float $max): array
    {
        $stored = $shop->rider_vehicle_limits ?? [];
        $limits = [];

        foreach ($types as $type) {
            $limits[$type] = $this->cleanLimit($stored[$type] ?? []);
        }

        if ($given === null) {
            $limits[$selected] = $this->cleanLimit(['min' => $min, 'max' => $max]);

            return $limits;
        }

        foreach ($given as $entry) {
            if (array_key_exists($entry['type'], $limits)) {
                $limits[$entry['type']] = $this->cleanLimit($entry);
            }
        }

        return $limits;
    }

    /**
     * @param  array{min?: mixed, max?: mixed}  $limit
     * @return array{min: float|null, max: float|null}
     */
    private function cleanLimit(array $limit): array
    {
        return [
            'min' => is_numeric($limit['min'] ?? null) ? round((float) $limit['min'], 2) : null,
            'max' => is_numeric($limit['max'] ?? null) ? round((float) $limit['max'], 2) : null,
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
