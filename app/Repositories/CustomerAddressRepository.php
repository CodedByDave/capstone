<?php

namespace App\Repositories;

use App\Models\CustomerAddress;
use Illuminate\Support\Collection;

class CustomerAddressRepository extends Repository
{
    public function __construct(CustomerAddress $model)
    {
        parent::__construct($model);
    }

    /**
     * The customer's saved addresses, default first.
     *
     * @return Collection<int, CustomerAddress>
     */
    public function forUser(int $userId): Collection
    {
        return CustomerAddress::query()
            ->where('user_id', $userId)
            ->orderByDesc('is_default')
            ->orderBy('id')
            ->get();
    }

    /** Scoped to the owner: another customer's address is indistinguishable from a missing one. */
    public function findForUser(int $userId, string $publicId): ?CustomerAddress
    {
        return CustomerAddress::query()
            ->where('user_id', $userId)
            ->where('public_id', $publicId)
            ->first();
    }

    /**
     * Locks the customer's rows so two requests cannot both pass the limit check.
     *
     * @return Collection<int, CustomerAddress>
     */
    public function lockForUser(int $userId): Collection
    {
        return CustomerAddress::query()
            ->where('user_id', $userId)
            ->orderBy('id')
            ->lockForUpdate()
            ->get();
    }

    public function clearDefault(int $userId, ?int $exceptId = null): void
    {
        CustomerAddress::query()
            ->where('user_id', $userId)
            ->where('is_default', true)
            ->when($exceptId, fn ($query) => $query->where('id', '!=', $exceptId))
            ->update(['is_default' => false]);
    }
}
