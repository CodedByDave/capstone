<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\EmployeeRepository;
use App\Repositories\ShopRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Resolves the shop the acting user works for: the owner's own shop, or the shop of a staff member.
 * The shop is always derived from the authenticated user, never from request input.
 */
class ShopContext
{
    public function __construct(
        private readonly ShopRepository $shops,
        private readonly EmployeeRepository $employees,
    ) {}

    public function shopIdFor(User $user): int
    {
        $shopId = $user->isOwner()
            ? $this->shops->findByOwnerId($user->id)?->id
            : $this->employees->query()->where('user_id', $user->id)->value('shop_id');

        if ($shopId === null) {
            throw (new ModelNotFoundException)->setModel('Shop');
        }

        return (int) $shopId;
    }
}
