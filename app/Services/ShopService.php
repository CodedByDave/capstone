<?php

namespace App\Services;

use App\Repositories\ShopRepository;
use Illuminate\Support\Facades\DB;

class ShopService
{
    public function __construct(
        protected ShopRepository $shopRepo,
        protected UserService $userService
    ) {}

    public function registerShop(array $data)
    {
        return DB::transaction(function () use ($data) {

            $owner = $this->userService->createOwner([
                'name' => $data['owner_name'],
                'email' => $data['email'],
                'password' => $data['password'],
            ]);

            return $this->shopRepo->createWithOwner(
                ownerId: $owner->id,
                shopData: [
                    'shop_name' => $data['shop_name'],
                    'phone' => $data['phone'],
                    'address' => $data['address'],
                    'business_license' => $data['business_license'],
                ]
            );
        });
    }
}
