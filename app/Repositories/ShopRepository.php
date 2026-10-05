<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\Shop;

class ShopRepository extends Repository
{
    public function __construct(Shop $shop)
    {
        parent::__construct($shop);
    }

    public function createWithOwner(int $ownerId, array $shopData): Shop
    {
        return $this->create([
            'owner_id' => $ownerId,
            ...$shopData,
        ]);
    }

    public function findByOwnerId(int $ownerId): ?Shop
    {
        return Shop::query()->where('owner_id', $ownerId)->first();
    }

    public function findShopById(int $shopId): ?Shop
    {
        return Shop::query()->find($shopId);
    }

    public function updateSetup(Shop $shop, array $data): Shop
    {
        $shop->update($data);

        return $shop->refresh();
    }

    public function syncFromApprovedOrder(Order $order): Shop
    {
        $existingShop = Shop::withTrashed()->where('owner_id', $order->user_id)->first();

        return Shop::withTrashed()->updateOrCreate(
            ['owner_id' => $order->user_id],
            [
                'shop_name' => $order->shop_name,
                'phone' => $order->phone,
                'block_street' => $order->block_street,
                'municipality' => $order->municipality,
                'barangay' => $order->barangay,
                'postal_code' => $order->postal_code,
                'bir_expiry_date' => $order->bir_expiry_date,
                'dti_expiry_date' => $order->dti_expiry_date,
                'mayors_expiry_date' => $order->mayors_expiry_date,
                'sanitary_expiry_date' => $order->sanitary_expiry_date,
                'status' => $existingShop?->setup_completed_at ? 'active' : 'pending_setup',
                'deleted_at' => null,
            ],
        );
    }

    public function activateForTrial(int $ownerId, array $data): Shop
    {
        $existingShop = Shop::withTrashed()->where('owner_id', $ownerId)->first();

        return Shop::withTrashed()->updateOrCreate(
            ['owner_id' => $ownerId],
            [
                'shop_name' => $data['shop_name'],
                'phone' => $data['phone'],
                'block_street' => $data['block_street'] ?? null,
                'municipality' => $data['municipality'],
                'barangay' => $data['barangay'],
                'postal_code' => $data['postal_code'],
                'status' => $existingShop?->setup_completed_at ? 'active' : 'pending_setup',
                'deleted_at' => null,
            ],
        );
    }
}
