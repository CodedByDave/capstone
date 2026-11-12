<?php

namespace App\Services;

use App\Exceptions\DeliveryNotAvailableException;
use App\Models\Shop;
use App\Repositories\ShopRepository;

/**
 * What a rider pickup booked online costs the customer. It is priced with the shop's delivery
 * pricing (a fixed fee, or per kilometer from the customer's pinned address), so a shop sets one
 * price for its riders. A shop that has not set delivery pricing yet collects for free.
 */
class PickupFeeService
{
    public function __construct(
        private readonly ShopRepository $shops,
        private readonly CounterDeliveryFeeService $quotes,
    ) {}

    /**
     * The pickup fee for the shop and the customer's pinned address.
     *
     * @throws DeliveryNotAvailableException when per-kilometer pricing has no pin, or the address is out of range
     */
    public function feeFor(int $shopId, ?float $lat, ?float $lng): float
    {
        $shop = $this->shops->findShopById($shopId);

        if ($shop === null || ! $shop->canDeliver()) {
            return 0.0;
        }

        if ($shop->delivery_fee_mode === Shop::DELIVERY_FEE_PER_KM && ($lat === null || $lng === null)) {
            throw new DeliveryNotAvailableException(
                'We could not find your pickup address on the map. Check the address or use your current location so the pickup fee can be worked out.',
            );
        }

        return round((float) $this->quotes->quote($shopId, $lat, $lng)['fee'], 2);
    }

    /**
     * The pickup fee for an estimate shown before the order is placed. A per-kilometer fee without a
     * pin yet, or an address out of range, comes back as a note instead of a fee.
     *
     * @return array{fee: float, note: string|null}
     */
    public function estimateFor(int $shopId, ?float $lat, ?float $lng): array
    {
        $shop = $this->shops->findShopById($shopId);

        if ($shop === null || ! $shop->canDeliver()) {
            return ['fee' => 0.0, 'note' => null];
        }

        if ($shop->delivery_fee_mode === Shop::DELIVERY_FEE_PER_KM && ($lat === null || $lng === null)) {
            return ['fee' => 0.0, 'note' => 'The pickup fee depends on the distance and is added once your address is found on the map.'];
        }

        try {
            return ['fee' => $this->feeFor($shopId, $lat, $lng), 'note' => null];
        } catch (DeliveryNotAvailableException $exception) {
            return ['fee' => 0.0, 'note' => $exception->getMessage()];
        }
    }
}
