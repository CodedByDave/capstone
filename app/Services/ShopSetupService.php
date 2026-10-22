<?php

namespace App\Services;

use App\Exceptions\ShopSetupException;
use App\Models\Order;
use App\Models\Shop;
use App\Models\User;
use App\Repositories\BranchRepository;
use App\Repositories\EmployeeRepository;
use App\Repositories\OrderRepository;
use App\Repositories\ShopRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ShopSetupService
{
    public function __construct(
        private readonly ShopRepository $shops,
        private readonly OrderRepository $orders,
        private readonly EmployeeRepository $employees,
        private readonly BranchRepository $branches,
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
                'ownership_type' => $shop->ownership_type ?? Shop::OWNERSHIP_INDEPENDENT,
                'franchise_brand_name' => $shop->franchise_brand_name ?? '',
                'franchisor_name' => $shop->franchisor_name ?? '',
                'branches_count' => $this->branches->countForShop($shop->id),
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

        $newBranches = $data['branches'] ?? [];
        if ($data['location_mode'] === Shop::LOCATION_MULTIPLE
            && $this->branches->countForShop($shop->id) === 0
            && $newBranches === []) {
            throw new ShopSetupException('Add at least one branch with its location and permits.');
        }

        return DB::transaction(function () use ($shop, $owner, $data, $newBranches): Shop {
            $isFranchise = $data['ownership_type'] === Shop::OWNERSHIP_FRANCHISEE;
            $updatedShop = $this->shops->updateSetup($shop, [
                'ownership_type' => $data['ownership_type'],
                'franchise_brand_name' => $isFranchise ? $data['franchise_brand_name'] : null,
                'franchisor_name' => $isFranchise ? $data['franchisor_name'] : null,
                'location_mode' => $data['location_mode'],
                'offers_pickup' => (bool) $data['offers_pickup'],
                'offers_delivery' => (bool) $data['offers_delivery'],
                'setup_completed_at' => $shop->setup_completed_at ?? now(),
                'status' => 'active',
            ]);

            foreach ($newBranches as $branchData) {
                $this->createBranch($shop->id, $owner->id, $branchData);
            }

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
            'multiple_locations' => $shop->location_mode === Shop::LOCATION_MULTIPLE,
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
            'branches' => $shop->location_mode === Shop::LOCATION_MULTIPLE,
            'pickup' => (bool) $shop->offers_pickup,
            // Delivery runs only once its pricing is set, so no delivery is given away free.
            'delivery' => $shop->canDeliver(),
            // Setting the pricing itself needs only the choice to deliver.
            'delivery-setup' => (bool) $shop->offers_delivery,
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

    private function createBranch(int $shopId, int $ownerId, array $data): void
    {
        $branch = $this->branches->createForShop($shopId, [
            'branch_code' => 'B-'.Str::upper(Str::random(10)),
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'address' => collect([
                $data['block_street'],
                $data['barangay'],
                $data['municipality'],
                $data['postal_code'],
            ])->filter()->implode(', '),
            'block_street' => $data['block_street'],
            'municipality' => $data['municipality'],
            'barangay' => $data['barangay'],
            'postal_code' => $data['postal_code'],
            'mayors_permit_number' => $data['mayors_permit_number'],
            'mayors_expiry_date' => $data['mayors_expiry_date'],
            'sanitary_permit_number' => $data['sanitary_permit_number'] ?? null,
            'sanitary_expiry_date' => $data['sanitary_expiry_date'] ?? null,
            'permit_status' => 'submitted',
            'status' => 'Active',
            'created_by' => $ownerId,
            'updated_by' => $ownerId,
        ]);

        $paths = [];
        foreach (['mayors_permit', 'sanitary_permit'] as $permit) {
            $file = $data[$permit] ?? null;
            if ($file instanceof UploadedFile) {
                $path = $file->store("branch-permits/{$branch->id}", 'private');
                if (! $path) {
                    throw new ShopSetupException('A branch permit could not be stored. Please try again.');
                }
                $paths["{$permit}_path"] = $path;
            }
        }

        if ($paths !== []) {
            $this->branches->update($branch, $paths);
        }
    }
}
