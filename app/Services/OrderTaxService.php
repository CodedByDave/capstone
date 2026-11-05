<?php

namespace App\Services;

use App\Repositories\ShopOrderRepository;

/**
 * The tax added to an order. The shop's tax type comes from its verified BIR certificate:
 * a VAT-registered shop adds 12%, a non-VAT shop adds 3% percentage tax. The tax is charged on
 * the services and add-ons after discounts; the delivery fee is not taxed.
 */
class OrderTaxService
{
    public const VAT_RATE = 12.0;

    public const NON_VAT_RATE = 3.0;

    public function __construct(private readonly ShopOrderRepository $orders) {}

    /** The rate (in percent) for a tax type; a shop whose type is unknown charges no tax. */
    public function rateFor(?string $taxType): float
    {
        return match ($taxType) {
            'vat' => self::VAT_RATE,
            'non_vat' => self::NON_VAT_RATE,
            default => 0.0,
        };
    }

    public function rateForShop(int $shopId): float
    {
        return $this->rateFor($this->orders->taxTypeForShop($shopId));
    }

    /** The tax on an amount, rounded to the centavo (half up, worked in whole centavos). */
    public function amountOn(float $taxable, float $rate): float
    {
        if ($rate <= 0 || $taxable <= 0) {
            return 0.0;
        }

        $centavos = (int) round($taxable * 100);

        return round($centavos * $rate / 100) / 100;
    }

    /** The label shown on the bill, or null when no tax applies. */
    public function labelFor(float $rate): ?string
    {
        if ($rate <= 0) {
            return null;
        }

        $percent = rtrim(rtrim(number_format($rate, 2, '.', ''), '0'), '.');

        return match (true) {
            abs($rate - self::VAT_RATE) < 0.001 => "VAT ({$percent}%)",
            abs($rate - self::NON_VAT_RATE) < 0.001 => "Percentage tax ({$percent}%)",
            default => "Tax ({$percent}%)",
        };
    }

    /** @return array{rate: float, label: string|null} */
    public function forShop(int $shopId): array
    {
        $rate = $this->rateForShop($shopId);

        return ['rate' => $rate, 'label' => $this->labelFor($rate)];
    }
}
