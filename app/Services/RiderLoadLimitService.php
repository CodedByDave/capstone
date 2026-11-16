<?php

namespace App\Services;

use App\Models\Rider;
use App\Models\Shop;
use App\Models\ShopOrder;
use App\Repositories\RiderRepository;

/**
 * The weight range one rider run can carry, as set by the shop owner. Each run carries one order,
 * so an order is checked as a whole: the main service plus any extra weighed services. Orders
 * priced per piece have no weight and are never held back. Both ends of the range are allowed.
 */
class RiderLoadLimitService
{
    public const PURPOSE_PICKUP = 'pickup';

    public const PURPOSE_DELIVERY = 'delivery';

    public function __construct(private readonly RiderRepository $riders) {}

    /**
     * The vehicles that set the limit: those of the riders on duty now. When nobody is on duty,
     * every active rider's vehicle counts, so a run is not held wrongly overnight; with no riders
     * at all, the default vehicle.
     *
     * @return list<string>
     */
    private function fleetTypes(Shop $shop): array
    {
        return $this->riders->onDutyVehicleTypes($shop->id)
            ?: $this->riders->activeVehicleTypes($shop->id)
            ?: [Shop::DEFAULT_RIDER_VEHICLE_TYPE];
    }

    /**
     * What each vehicle of the riders on duty can carry, so a form can show the range per
     * vehicle instead of one merged range.
     *
     * @return list<array{type: string, min: float|null, max: float|null}>
     */
    public function vehicleRangesFor(?Shop $shop): array
    {
        if ($shop === null) {
            return [];
        }

        $types = $this->fleetTypes($shop);

        return array_map(fn (string $type) => ['type' => $type] + $this->limitForType($shop, $type), $types);
    }

    /**
     * What a booking is held to: the range the riders on duty can cover between them. A booking is
     * made before any rider is chosen, so it only has to fit at least one active rider's vehicle:
     * the loosest minimum and the largest maximum across the fleet. A side that any vehicle leaves
     * open stays open. A shop with no active rider uses the default vehicle's capacity.
     *
     * @return array{min: float|null, max: float|null}
     */
    public function limitsFor(?Shop $shop): array
    {
        if ($shop === null) {
            return ['min' => null, 'max' => null];
        }

        $types = $this->fleetTypes($shop);
        $limits = array_map(fn (string $type) => $this->limitForType($shop, $type), $types);

        $mins = array_column($limits, 'min');
        $maxes = array_column($limits, 'max');

        return [
            'min' => in_array(null, $mins, true) ? null : min($mins),
            'max' => in_array(null, $maxes, true) ? null : max($maxes),
        ];
    }

    /**
     * The capacity of one vehicle type. A type with no saved capacity, such as one that was removed
     * from Settings, or a rider with no type, is held to the default vehicle's capacity.
     *
     * @return array{min: float|null, max: float|null}
     */
    public function limitForType(?Shop $shop, ?string $type): array
    {
        $stored = $shop?->rider_vehicle_limits ?? [];
        $type = filled($type) ? (string) $type : Shop::DEFAULT_RIDER_VEHICLE_TYPE;

        return $this->cleanLimit($stored[$type] ?? $stored[Shop::DEFAULT_RIDER_VEHICLE_TYPE] ?? []);
    }

    /** @return array{min: float|null, max: float|null} the capacity of the vehicle this rider drives */
    public function limitForRider(?Shop $shop, ?Rider $rider): array
    {
        return $this->limitForType($shop, $rider?->vehicle_type);
    }

    /**
     * Why this rider's vehicle cannot carry the weight, or null when it can. Unknown weights and
     * vehicles without limits always can.
     */
    public function riderViolation(?Shop $shop, Rider $rider, ?float $weight): ?string
    {
        if ($weight === null) {
            return null;
        }

        ['min' => $min, 'max' => $max] = $this->limitForRider($shop, $rider);
        $vehicle = filled($rider->vehicle_type) ? $rider->vehicle_type : Shop::DEFAULT_RIDER_VEHICLE_TYPE;

        return match (true) {
            $max !== null && $weight > $max => $rider->name.' drives a '.$vehicle.', which carries up to '.$this->kg($max).'. This laundry is '.$this->kg($weight).'.',
            $min !== null && $weight < $min => $rider->name.' drives a '.$vehicle.', which needs at least '.$this->kg($min).' of laundry. This order is '.$this->kg($weight).'.',
            default => null,
        };
    }

    /**
     * The vehicle types that can carry the weight, to pick a rider for a run. Null means every
     * vehicle can (an unknown weight).
     *
     * @return list<string>|null
     */
    public function vehicleTypesThatCarry(?Shop $shop, ?float $weight): ?array
    {
        if ($weight === null || $shop === null) {
            return null;
        }

        $types = array_unique([Shop::DEFAULT_RIDER_VEHICLE_TYPE, ...($shop->rider_vehicle_types ?: [])]);

        return array_values(array_filter($types, function (string $type) use ($shop, $weight): bool {
            ['min' => $min, 'max' => $max] = $this->limitForType($shop, $type);

            return ($max === null || $weight <= $max) && ($min === null || $weight >= $min);
        }));
    }

    /**
     * The vehicle types and what each can carry, for the Settings page.
     *
     * @return array{choices: list<string>, limits: list<array{type: string, min: float|null, max: float|null}>}
     */
    public function vehicleOptionsFor(Shop $shop): array
    {
        $choices = $shop->rider_vehicle_types ?: [Shop::DEFAULT_RIDER_VEHICLE_TYPE];
        $stored = $shop->rider_vehicle_limits ?? [];

        return [
            'choices' => $choices,
            'limits' => array_map(fn (string $type) => ['type' => $type] + $this->cleanLimit($stored[$type] ?? []), $choices),
        ];
    }

    /**
     * The capacity of every vehicle type after a save, keyed by type. Types no longer offered are
     * dropped; a type that was not given keeps what it had.
     *
     * @param  list<string>  $types
     * @param  list<array{type: string, min?: mixed, max?: mixed}>  $given
     * @return array<string, array{min: float|null, max: float|null}>
     */
    public function vehicleLimitsAfterSave(Shop $shop, array $types, array $given): array
    {
        $stored = $shop->rider_vehicle_limits ?? [];
        $limits = [];

        foreach ($types as $type) {
            $limits[$type] = $this->cleanLimit($stored[$type] ?? []);
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
