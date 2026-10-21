<?php

namespace App\Services;

use App\Models\ShopOrder;
use App\Models\ShopService;
use App\Repositories\ShopOrderRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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
        $isWeighed = $order->actual_weight_kg !== null;

        $weight = (float) ($isWeighed ? $order->actual_weight_kg : $order->estimated_weight_kg);
        $bundleWeight = (float) $order->bundle_weight_kg;

        $quantity = $isPerKg
            ? $weight
            : (float) ($order->bundle_quantity ?? $this->bundlesFor($weight, $bundleWeight));
        $rate = (float) ($isPerKg ? $order->price_per_kg : $order->bundle_price);

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
            'discount' => (float) $order->discount_amount,
            'total' => $total,
            'amount_paid' => $amountPaid,
            'balance_due' => round(max(0, $total - $amountPaid), 2),
        ];
    }

    /**
     * Estimate for a service at an estimated weight, using the same rounding the order uses at placement.
     */
    public function estimate(ShopService $service, float $weightKg): array
    {
        $isPerKg = $service->pricing_model === 'per_kg';
        $bundleWeight = (float) $service->bundle_weight_kg;

        $quantity = $isPerKg ? $weightKg : (float) $this->bundlesFor($weightKg, $bundleWeight);
        $rate = (float) ($isPerKg ? $service->price_per_kg : $service->bundle_price);
        $line = $this->serviceLine($service->service_name, $service->pricing_model, $quantity, $rate, $bundleWeight);

        return [
            'is_estimate' => true,
            'service' => $line,
            'additional_charges' => 0.0,
            'discount' => 0.0,
            'total' => $line['amount'],
            'amount_paid' => 0.0,
            'balance_due' => $line['amount'],
        ];
    }

    /**
     * Estimate for an active service of an active shop; anything else is treated as not found.
     */
    public function estimateFor(int $shopId, int $serviceId, float $weightKg): array
    {
        $shop = $this->repository->findActiveShop($shopId);
        $service = $shop ? $this->repository->findActiveServiceForShop($serviceId, $shop->id) : null;

        if (! $service) {
            throw (new ModelNotFoundException)->setModel(ShopService::class);
        }

        return $this->estimate($service, $weightKg);
    }

    private function serviceLine(string $name, string $pricingModel, float $quantity, float $rate, float $bundleWeight): array
    {
        $isPerKg = $pricingModel === 'per_kg';

        return [
            'name' => $name,
            'pricing_model' => $pricingModel,
            'quantity' => $quantity,
            'unit' => $isPerKg ? 'kg' : 'bundle',
            'rate' => $rate,
            'bundle_weight_kg' => $isPerKg ? null : $bundleWeight,
            'amount' => round($quantity * $rate, 2),
        ];
    }

    private function bundlesFor(float $weightKg, float $bundleWeightKg): int
    {
        return max(1, (int) ceil($weightKg / max($bundleWeightKg, 0.01)));
    }
}
