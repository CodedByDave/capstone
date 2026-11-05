<?php

namespace App\Services;

use App\Exceptions\DeliveryNotAvailableException;
use App\Models\Shop;
use App\Models\User;
use App\Repositories\ShopRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Shop-scoped map operations: who may use a shop's map data, delivery fee quotes and route
 * optimisation. Fees are always computed here from the shop's own settings, never from client input.
 */
class ShopMapService
{
    public function __construct(
        private readonly ShopRepository $shops,
        private readonly ShopContext $shopContext,
        private readonly MapboxService $mapbox,
        private readonly DeliveryFeeService $fees,
    ) {}

    /**
     * The shop's own staff and owner may use their shop; customers may use any active shop that
     * offers delivery. Anything else is indistinguishable from a missing shop.
     *
     * @throws ModelNotFoundException
     */
    public function shopFor(User $user, string $publicId): Shop
    {
        if ($user->isOwner() || $user->isStaff()) {
            $shop = $this->shops->findShopById($this->shopContext->shopIdFor($user));

            if ($shop !== null && $shop->public_id === $publicId) {
                return $shop;
            }
        } elseif ($user->isUser()) {
            $shop = $this->shops->findActiveByPublicId($publicId);

            if ($shop !== null && $shop->offers_delivery) {
                return $shop;
            }
        }

        throw (new ModelNotFoundException)->setModel(Shop::class);
    }

    /**
     * Driving route from the shop to a destination.
     *
     * @return array<string, mixed>
     *
     * @throws DeliveryNotAvailableException
     */
    public function route(Shop $shop, float $toLat, float $toLng): array
    {
        $this->assertHasLocation($shop);

        return $this->mapbox->route((string) $shop->id, (float) $shop->latitude, (float) $shop->longitude, $toLat, $toLng);
    }

    /**
     * Delivery fee for a destination. Fixed-rate shops are priced without any Mapbox call.
     *
     * @return array<string, mixed>
     *
     * @throws DeliveryNotAvailableException
     */
    public function feeQuote(Shop $shop, float $toLat, float $toLng): array
    {
        $this->fees->assertShopCanQuote($shop);

        if ($shop->delivery_fee_mode !== Shop::DELIVERY_FEE_PER_KM) {
            $breakdown = $this->fees->breakdown($shop, 0.0);

            return [
                'fee' => $breakdown['fee'],
                'fee_type' => $breakdown['mode'],
                'distance_m' => null,
                'duration_s' => null,
                'breakdown' => $breakdown,
            ];
        }

        $route = $this->route($shop, $toLat, $toLng);
        $breakdown = $this->fees->breakdown($shop, $route['distance_m'] / 1000);

        return [
            'fee' => $breakdown['fee'],
            'fee_type' => $breakdown['mode'],
            'distance_m' => $route['distance_m'],
            'duration_s' => $route['duration_s'],
            'breakdown' => $breakdown,
        ];
    }

    /**
     * Best order to visit several stops, starting at the shop.
     *
     * @param  list<array{lat: float, lng: float}>  $stops
     * @return array<string, mixed>
     *
     * @throws DeliveryNotAvailableException
     */
    public function optimize(Shop $shop, array $stops): array
    {
        $this->assertHasLocation($shop);

        $result = $this->mapbox->optimize((string) $shop->id, (float) $shop->latitude, (float) $shop->longitude, $stops);

        return [
            ...$result,
            'stops' => array_map(
                fn (int $index, int $position): array => ['index' => $index, 'sequence' => $position + 1, ...$stops[$index]],
                $result['order'],
                array_keys($result['order']),
            ),
        ];
    }

    private function assertHasLocation(Shop $shop): void
    {
        if (! $shop->hasCoordinates()) {
            throw new DeliveryNotAvailableException('The shop has not set its location yet.');
        }
    }
}
