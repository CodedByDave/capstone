<?php

namespace App\Services;

use App\Models\ShopOrder;
use App\Models\ShopService;
use App\Repositories\ShopOrderRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

/**
 * Builds the customer-facing itemized billing for an order or a not-yet-placed estimate.
 * It is the single place that turns weight and rate into a service amount.
 */
class OrderBillingBreakdownService
{
    public function __construct(private readonly ShopOrderRepository $repository) {}

    public function forOrder(ShopOrder $order): array
    {
        $isPerKg = $order->pricing_model === 'per_kg';
        $isPerPiece = $order->pricing_model === 'per_piece';
        // A per-piece price is final from the start: nothing is weighed.
        $isWeighed = $order->actual_weight_kg !== null || $isPerPiece;

        $weight = (float) ($order->actual_weight_kg !== null ? $order->actual_weight_kg : $order->estimated_weight_kg);
        $bundleWeight = (float) $order->bundle_weight_kg;

        $quantity = match (true) {
            $isPerKg => $weight,
            $isPerPiece => (float) $order->piece_count,
            default => (float) ($order->bundle_quantity ?? $this->bundlesFor($weight, $bundleWeight)),
        };
        $rate = (float) match (true) {
            $isPerKg => $order->price_per_kg,
            $isPerPiece => $order->price_per_piece,
            default => $order->bundle_price,
        };

        $total = (float) $order->total_amount;
        $amountPaid = (float) $order->amount_paid;

        return [
            'is_estimate' => ! $isWeighed,
            'service' => $this->serviceLine(
                $order->service?->service_name ?? 'Laundry service',
                $order->pricing_model,
                $quantity,
                $rate,
                $bundleWeight,
            ),
            'additional_charges' => (float) $order->additional_charges,
            ...$this->additionalChargeDetail($order),
            'discount' => (float) $order->discount_amount,
            'delivery_fee' => (float) $order->delivery_fee,
            'total' => $total,
            'amount_paid' => $amountPaid,
            'balance_due' => round(max(0, $total - $amountPaid), 2),
        ];
    }

    /**
     * Which supplies the additional charges come from, so the customer can see what they paid for.
     * Each line is priced at what the supply cost when it was added; a supply from before prices were
     * recorded has no amount. `other_charges` is what the supplies do not explain (a manual charge or
     * adjustment), or null when that cannot be worked out.
     *
     * @return array{supplies: list<array<string, mixed>>, other_charges: float|null}
     */
    public function additionalChargeDetail(ShopOrder $order): array
    {
        $lines = $order->supplies->map(function ($supply): array {
            $quantity = (float) $supply->pivot->quantity_used;
            $price = $supply->pivot->unit_price !== null ? (float) $supply->pivot->unit_price : null;

            return [
                'name' => $supply->name,
                'quantity' => $quantity,
                'unit' => $supply->pivot->unit ?: $supply->unit,
                'unit_price' => $price,
                'amount' => $price !== null ? round($price * $quantity, 2) : null,
            ];
        })->values();

        $allPriced = $lines->every(fn (array $line) => $line['amount'] !== null);
        $other = $allPriced
            ? round((float) $order->additional_charges - (float) $lines->sum('amount'), 2)
            : null;

        return [
            'supplies' => $lines->all(),
            'other_charges' => $other !== null && abs($other) < 0.005 ? 0.0 : $other,
        ];
    }

    /**
     * Estimate for a service at an estimated weight, using the same rounding the order uses at placement.
     */
    public function estimate(ShopService $service, float $weightKg, ?int $pieces = null): array
    {
        $bundleWeight = (float) $service->bundle_weight_kg;

        $quantity = match ($service->pricing_model) {
            'per_kg' => $weightKg,
            'per_piece' => (float) max(1, (int) $pieces),
            default => (float) $this->bundlesFor($weightKg, $bundleWeight),
        };
        $rate = (float) match ($service->pricing_model) {
            'per_kg' => $service->price_per_kg,
            'per_piece' => $service->price_per_piece,
            default => $service->bundle_price,
        };
        $line = $this->serviceLine($service->service_name, $service->pricing_model, $quantity, $rate, $bundleWeight);

        return [
            'is_estimate' => ! $service->isPerPiece(),
            'service' => $line,
            'additional_charges' => 0.0,
            'supplies' => [],
            'other_charges' => 0.0,
            'discount' => 0.0,
            'delivery_fee' => 0.0,
            'total' => $line['amount'],
            'amount_paid' => 0.0,
            'balance_due' => $line['amount'],
        ];
    }

    /**
     * Estimate for an active service of an active shop; anything else is treated as not found.
     */
    public function estimateFor(int $shopId, int $serviceId, float $weightKg, ?int $pieces = null): array
    {
        $shop = $this->repository->findActiveShop($shopId);
        $service = $shop ? $this->repository->findActiveServiceForShop($serviceId, $shop->id) : null;

        if (! $service) {
            throw (new ModelNotFoundException)->setModel(ShopService::class);
        }

        // The service decides what the customer has to tell us: a piece count, or a weight.
        if ($service->isPerPiece() && ! $pieces) {
            throw ValidationException::withMessages(['piece_count' => 'Enter how many pieces you are bringing.']);
        }

        if (! $service->isPerPiece() && $weightKg <= 0) {
            throw ValidationException::withMessages(['estimated_weight_kg' => 'Enter the estimated weight of your laundry.']);
        }

        return $this->estimate($service, $weightKg, $pieces);
    }

    private function serviceLine(string $name, string $pricingModel, float $quantity, float $rate, float $bundleWeight): array
    {
        $isBundle = $pricingModel === 'per_bundle';

        return [
            'name' => $name,
            'pricing_model' => $pricingModel,
            'quantity' => $quantity,
            'unit' => match ($pricingModel) {
                'per_kg' => 'kg',
                'per_piece' => 'piece',
                default => 'bundle',
            },
            'rate' => $rate,
            'bundle_weight_kg' => $isBundle ? $bundleWeight : null,
            'amount' => round($quantity * $rate, 2),
        ];
    }

    private function bundlesFor(float $weightKg, float $bundleWeightKg): int
    {
        return max(1, (int) ceil($weightKg / max($bundleWeightKg, 0.01)));
    }
}
