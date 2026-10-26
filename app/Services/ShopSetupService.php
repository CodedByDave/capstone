<?php

namespace App\Services;

use App\Exceptions\ShopSetupException;
use App\Models\Order;
use App\Models\Shop;
use App\Models\User;
use App\Repositories\EmployeeRepository;
use App\Repositories\OrderRepository;
use App\Repositories\ShopRepository;
use Illuminate\Support\Facades\DB;

class ShopSetupService
{
    public function __construct(
        private readonly ShopRepository $shops,
        private readonly OrderRepository $orders,
        private readonly EmployeeRepository $employees,
    ) {}

    public function pageData(User $owner): array
    {
        $shop = $this->shopForOwner($owner);
        $order = $this->activeSubscription($owner);

        return [
            'shop' => [
                'shop_name' => $shop->shop_name,
                'phone' => $shop->phone,
                'address' => collect([
                    $shop->block_street,
                    $shop->barangay,
                    $shop->municipality,
                    $shop->postal_code,
                ])->filter()->implode(', '),
                'offers_pickup' => (bool) ($shop->offers_pickup ?? false),
                'offers_delivery' => (bool) ($shop->offers_delivery ?? false),
                'ownership_type' => $shop->ownership_type ?? Shop::OWNERSHIP_INDEPENDENT,
                'franchise_brand_name' => $shop->franchise_brand_name ?? '',
                'franchisor_name' => $shop->franchisor_name ?? '',
                'setup_completed' => $shop->setup_completed_at !== null,
            ],
            'plan' => [
                'name' => $order->is_trial ? 'Trial' : $order->plan_name,
                'is_trial' => $order->is_trial,
            ],
        ];
    }

    public function complete(User $owner, array $data): Shop
    {
        $shop = $this->shopForOwner($owner);
        $this->activeSubscription($owner);

        return DB::transaction(function () use ($shop, $data): Shop {
            $isFranchise = $data['ownership_type'] === Shop::OWNERSHIP_FRANCHISEE;
            $updatedShop = $this->shops->updateSetup($shop, [
                'ownership_type' => $data['ownership_type'],
                'franchise_brand_name' => $isFranchise ? $data['franchise_brand_name'] : null,
                'franchisor_name' => $isFranchise ? $data['franchisor_name'] : null,
                'offers_pickup' => (bool) $data['offers_pickup'],
                'offers_delivery' => (bool) $data['offers_delivery'],
                'setup_completed_at' => $shop->setup_completed_at ?? now(),
                'status' => 'active',
            ]);

            return $updatedShop;
        });
    }

    public function needsSetup(User $owner): bool
    {
        $shop = $this->shops->findByOwnerId($owner->id);

        return $shop !== null
            && $shop->setup_completed_at === null
            && $this->orders->findActiveSubscriptionForUser($owner->id) !== null;
    }

    public function needsDeliveryPricing(User $owner): bool
    {
        $shop = $this->shops->findByOwnerId($owner->id);

        return $shop !== null
            && $shop->setup_completed_at !== null
            && $shop->needsDeliveryPricing();
    }

    public function capabilitiesForOwner(User $owner): ?array
    {
        $shop = $this->shops->findByOwnerId($owner->id);

        return $shop ? $this->capabilitiesForShop($shop) : null;
    }

    public function capabilitiesForUser(User $user): ?array
    {
        if ($user->role === 'owner') {
            return $this->capabilitiesForOwner($user);
        }

        if ($user->role !== 'staff') {
            return null;
        }

        $shopId = $this->employees->query()
            ->where('user_id', $user->id)
            ->value('shop_id');
        $shop = $shopId ? $this->shops->findShopById((int) $shopId) : null;

        return $shop ? $this->capabilitiesForShop($shop) : null;
    }

    public function capabilitiesForShop(Shop $shop): array
    {
        return [
            'setup_completed' => $shop->setup_completed_at !== null,
            'offers_pickup' => (bool) $shop->offers_pickup,
            'offers_delivery' => $shop->canDeliver(),
            'delivery_pricing_required' => $shop->needsDeliveryPricing(),
        ];
    }

    public function shopSupports(int $shopId, string $capability): bool
    {
        $shop = $this->shops->findShopById($shopId);

        if ($shop === null) {
            return false;
        }

        return match ($capability) {
            'pickup' => (bool) $shop->offers_pickup,
            // Delivery runs only once its pricing is set, so no delivery is given away free.
            'delivery' => $shop->canDeliver(),
            // Setting the pricing itself needs only the choice to deliver.
            'delivery-setup' => (bool) $shop->offers_delivery,
            // Order automation is a paid-plan feature; a free trial does not include it.
            'order-automation' => $this->orders->hasPaidSubscription((int) $shop->owner_id),
            default => false,
        };
    }

    private function shopForOwner(User $owner): Shop
    {
        return $this->shops->findByOwnerId($owner->id)
            ?? throw new ShopSetupException('No shop is associated with this owner account.');
    }

    private function activeSubscription(User $owner): Order
    {
        return $this->orders->findActiveSubscriptionForUser($owner->id)
            ?? throw new ShopSetupException('An active subscription or trial is required to configure the shop.');
    }
}
