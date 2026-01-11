<?php

namespace App\Repositories;

use App\Models\Shop;
use App\Models\User;

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
}
