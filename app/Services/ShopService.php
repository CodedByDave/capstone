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
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
            ]);

            $shop = $this->shopRepo->createWithOwner(
                $owner->id,
                [
                    'shop_name'    => $data['shop_name'],
                    'phone'        => $data['phone'],
                    'block_street' => $data['block_street'],
                    'municipality' => $data['municipality'],
                    'barangay'     => $data['barangay'],
                    'postal_code'  => $data['postal_code'],
                    'status'       => 'pending',
                ]
            );

            return compact('owner', 'shop');
        });
    }
}
