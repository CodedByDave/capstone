<?php

namespace App\Services;

use App\Models\ShopRole;
use App\Repositories\ShopRepository;
use Illuminate\Support\Facades\DB;

class ShopService
{
    public function __construct(
        protected ShopRepository $shopRepo,
        protected UserService    $userService
    ) {}

    public function registerShop(array $data)
    {
        return DB::transaction(function () use ($data) {

            $owner = $this->userService->createOwner([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => $data['password'],
            ]);

            $shop = $this->shopRepo->createWithOwner($owner->id, [
                'shop_name'    => $data['shop_name'],
                'phone'        => $data['phone'],
                'block_street' => $data['block_street'] ?? null,
                'municipality' => $data['municipality'],
                'barangay'     => $data['barangay'],
                'postal_code'  => $data['postal_code'],
                'latitude'     => $data['latitude'] ?? null,
                'longitude'    => $data['longitude'] ?? null,
                'status'       => 'pending',
            ]);

            $defaultRoles = ['owner', 'manager', 'cashier', 'washer', 'staff'];
            foreach ($defaultRoles as $role) {
                ShopRole::create([
                    'shop_id'    => $shop->id,
                    'name'       => $role,
                    'is_default' => true,
                ]);
            }

            return compact('owner', 'shop');
        });
    }
}
