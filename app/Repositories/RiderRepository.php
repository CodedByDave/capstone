<?php

namespace App\Repositories;

use App\Models\Delivery;
use App\Models\Rider;
use Illuminate\Database\Eloquent\Collection;

class RiderRepository extends Repository
{
    public function __construct(Rider $rider)
    {
        parent::__construct($rider);
    }

    public function activeForShop(int $shopId): Collection
    {
        $busy = $this->busyIdsForShop($shopId);

        return Rider::query()
            ->where('shop_id', $shopId)
            ->active()
            ->orderBy('name')
            ->get(['id', 'name', 'vehicle_type'])
            ->each(fn (Rider $rider) => $rider->setAttribute('available', ! in_array($rider->id, $busy, true)));
    }

    /** Delivery statuses during which a rider is out on a run and cannot take another. */
    private const BUSY_STATUSES = ['assigned', 'picked_up'];

    /** @return list<int> ids of the shop's riders currently on a delivery */
    public function busyIdsForShop(int $shopId): array
    {
        return Delivery::query()
            ->where('shop_id', $shopId)
            ->whereIn('status', self::BUSY_STATUSES)
            ->whereNotNull('rider_id')
            ->pluck('rider_id')
            ->unique()
            ->values()
            ->all();
    }

    /**
     * An active rider with no delivery on the road. The rider who has waited longest
     * since their last assignment is chosen so the work is shared. Meant to be called
     * inside the caller's transaction: the rows are locked while the choice is made.
     */
    public function findAvailableForShop(int $shopId): ?Rider
    {
        return Rider::query()
            ->where('shop_id', $shopId)
            ->active()
            ->whereDoesntHave('deliveries', fn ($query) => $query->whereIn('status', self::BUSY_STATUSES))
            ->withMax('deliveries', 'assigned_at')
            ->orderBy('deliveries_max_assigned_at')
            ->orderBy('id')
            ->lockForUpdate()
            ->first();
    }

    public function listForShop(int $shopId): Collection
    {
        return Rider::query()
            ->where('shop_id', $shopId)
            ->withCount('deliveries')
            ->orderBy('name')
            ->get();
    }

    public function createForShop(int $shopId, array $data): Rider
    {
        return Rider::query()->create(array_merge($data, ['shop_id' => $shopId]));
    }
}
