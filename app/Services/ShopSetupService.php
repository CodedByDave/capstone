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
        $allowsMultipleLocations = $this->allowsMultipleLocations($order);

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
                'location_mode' => $shop->location_mode === Shop::LOCATION_MULTIPLE && $allowsMultipleLocations
                    ? Shop::LOCATION_MULTIPLE
                    : Shop::LOCATION_SINGLE,
                'offers_pickup' => (bool) ($shop->offers_pickup ?? false),
                'offers_delivery' => (bool) ($shop->offers_delivery ?? false),
                'setup_completed' => $shop->setup_completed_at !== null,
            ],
            'plan' => [
                'name' => $order->is_trial ? 'Trial' : $order->plan_name,
                'is_trial' => $order->is_trial,
                'allows_multiple_locations' => $allowsMultipleLocations,
            ],
        ];
    }

    public function complete(User $owner, array $data): Shop
    {
        $shop = $this->shopForOwner($owner);
        $order = $this->activeSubscription($owner);

        if ($data['location_mode'] === Shop::LOCATION_MULTIPLE
            && ! $this->allowsMultipleLocations($order)) {
            throw new ShopSetupException('Multiple locations are available on the Premium plan.');
        }

        return DB::transaction(fn () => $this->shops->updateSetup($shop, [
            'location_mode' => $data['location_mode'],
            'offers_pickup' => (bool) $data['offers_pickup'],
            'offers_delivery' => (bool) $data['offers_delivery'],
            'setup_completed_at' => $shop->setup_completed_at ?? now(),
            'status' => 'active',
        ]));
    }

    public function needsSetup(User $owner): bool
    {
        $shop = $this->shops->findByOwnerId($owner->id);

        return $shop !== null
            && $shop->setup_completed_at === null
            && $this->orders->findActiveSubscriptionForUser($owner->id) !== null;
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
            'multiple_locations' => $shop->location_mode === Shop::LOCATION_MULTIPLE,
            'offers_pickup' => (bool) $shop->offers_pickup,
            'offers_delivery' => (bool) $shop->offers_delivery,
        ];
    }

    public function shopSupports(int $shopId, string $capability): bool
    {
        $shop = $this->shops->findShopById($shopId);

        if ($shop === null) {
            return false;
        }

        return match ($capability) {
            'branches' => $shop->location_mode === Shop::LOCATION_MULTIPLE,
            'pickup' => (bool) $shop->offers_pickup,
            'delivery' => (bool) $shop->offers_delivery,
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

    private function allowsMultipleLocations(Order $order): bool
    {
        return $order->is_trial || $order->plan_name === 'Premium';
    }
}
