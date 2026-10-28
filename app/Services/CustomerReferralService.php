<?php

namespace App\Services;

use App\Models\Shop;
use App\Models\User;
use App\Repositories\ShopRepository;
use App\Repositories\UserRepository;

/**
 * Customers join a laundry shop by scanning its QR code and stay tied to that shop, so a shop never
 * loses a customer it referred to another shop.
 */
class CustomerReferralService
{
    public function __construct(
        private readonly ShopRepository $shops,
        private readonly UserRepository $users,
    ) {}

    public function findJoinableShop(string $publicId): ?Shop
    {
        return $this->shops->findActiveByPublicId($publicId);
    }

    public function findJoinableShopById(?int $shopId): ?Shop
    {
        return $shopId ? $this->shops->findActiveById($shopId) : null;
    }

    /** The shop link a shop owner prints or shows as a QR code. */
    public function joinUrlFor(Shop $shop): string
    {
        $path = route('customer.join', ['publicId' => $shop->public_id], false);

        // A printed QR must keep pointing at the public address, even behind a proxy that hides the real host or scheme.
        return app()->isProduction()
            ? rtrim((string) config('app.url'), '/').$path
            : url($path);
    }

    /**
     * Ties a customer that has no shop yet to the scanned shop.
     *
     * @return bool false when the customer already belongs to a shop
     */
    public function bindCustomer(User $customer, Shop $shop): bool
    {
        if ($customer->referred_shop_id !== null) {
            return $customer->referred_shop_id === $shop->id;
        }

        $this->users->setReferredShop($customer, $shop->id);

        return true;
    }

    /** @return array{name: string, municipality: string|null, barangay: string|null} */
    public function summary(Shop $shop): array
    {
        return [
            'name' => $shop->shop_name,
            'municipality' => $shop->municipality,
            'barangay' => $shop->barangay,
        ];
    }
}
