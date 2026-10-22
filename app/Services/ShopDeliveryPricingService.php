<?php

namespace App\Services;

use App\Models\Shop;
use App\Models\User;
use App\Repositories\ShopRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * A shop owner's delivery pricing settings.
 */
class ShopDeliveryPricingService
{
    private const AMOUNT_FIELDS = [
        'delivery_fixed_fee',
        'delivery_base_fee',
        'delivery_per_km_rate',
        'delivery_free_km',
        'delivery_min_fee',
    ];

    public function __construct(private readonly ShopRepository $shops) {}

    public function pageData(User $owner): array
    {
        $shop = $this->shopForOwner($owner);

        return [
            'offers_delivery' => (bool) $shop->offers_delivery,
            'has_location' => $shop->hasCoordinates(),
            'delivery_fee_mode' => $shop->delivery_fee_mode ?: Shop::DELIVERY_FEE_FIXED,
            'delivery_fixed_fee' => (float) $shop->delivery_fixed_fee,
            'delivery_base_fee' => (float) $shop->delivery_base_fee,
            'delivery_per_km_rate' => (float) $shop->delivery_per_km_rate,
            'delivery_free_km' => (float) $shop->delivery_free_km,
            'delivery_min_fee' => (float) $shop->delivery_min_fee,
            'delivery_max_km' => $shop->delivery_max_km !== null ? (float) $shop->delivery_max_km : null,
        ];
    }

    /**
     * Saves the selected mode. Fields that belong to the other mode keep their stored
     * value when omitted, so switching modes back and forth does not lose settings.
     */
    public function update(User $owner, array $data): Shop
    {
        $shop = $this->shopForOwner($owner);

        $values = [
            'delivery_fee_mode' => $data['delivery_fee_mode'],
            'delivery_max_km' => isset($data['delivery_max_km']) ? round((float) $data['delivery_max_km'], 2) : null,
        ];

        foreach (self::AMOUNT_FIELDS as $field) {
            if (array_key_exists($field, $data)) {
                $values[$field] = round((float) ($data[$field] ?? 0), 2);
            }
        }

        return $this->shops->updateDeliveryPricing($shop, $values);
    }

    private function shopForOwner(User $owner): Shop
    {
        return $this->shops->findByOwnerId($owner->id)
            ?? throw (new ModelNotFoundException)->setModel(Shop::class);
    }
}
