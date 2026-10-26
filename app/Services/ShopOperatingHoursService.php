<?php

namespace App\Services;

use App\Models\Shop;
use App\Models\User;
use App\Repositories\ShopRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * A shop owner's operating hours: open around the clock, or only between two times of day.
 */
class ShopOperatingHoursService
{
    public function __construct(private readonly ShopRepository $shops) {}

    public function pageData(User $owner): array
    {
        $shop = $this->shopForOwner($owner);

        return [
            'is_open_24_7' => (bool) $shop->is_open_24_7,
            'opens_at' => $shop->opens_at ? substr($shop->opens_at, 0, 5) : '08:00',
            'closes_at' => $shop->closes_at ? substr($shop->closes_at, 0, 5) : '17:00',
        ];
    }

    public function update(User $owner, array $data): Shop
    {
        $shop = $this->shopForOwner($owner);
        $always = (bool) $data['is_open_24_7'];

        return $this->shops->updateDeliveryPricing($shop, [
            'is_open_24_7' => $always,
            // Keep the last custom hours when switching to 24/7, so switching back needs no retyping.
            'opens_at' => $always ? $shop->opens_at : $data['opens_at'],
            'closes_at' => $always ? $shop->closes_at : $data['closes_at'],
        ]);
    }

    private function shopForOwner(User $owner): Shop
    {
        return $this->shops->findByOwnerId($owner->id)
            ?? throw (new ModelNotFoundException)->setModel(Shop::class);
    }
}
