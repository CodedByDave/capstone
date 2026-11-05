<?php

namespace App\Services;

use App\Models\Shop;
use App\Models\User;
use App\Repositories\ShopRepository;
use App\Repositories\UserRepository;

/**
 * Customers sign up by scanning a laundry shop's QR code. The first shop is kept as their home shop
 * (and credited as the referrer), but they are free to browse and order from any nearby shop.
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
     * Remembers the first shop a customer scanned as their home shop. Scanning another shop later
     * never moves or blocks them: customers can browse and order from any active shop.
     */
    public function bindCustomer(User $customer, Shop $shop): void
    {
        if ($customer->referred_shop_id === null) {
            $this->users->setReferredShop($customer, $shop->id);
        }
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
