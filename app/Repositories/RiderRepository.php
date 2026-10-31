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
        $onDuty = $this->onDutyIdsForShop($shopId);

        return Rider::query()
            ->where('shop_id', $shopId)
            ->active()
            ->whereNotNull('employee_id')
            ->orderBy('name')
            ->get(['id', 'name', 'vehicle_type', 'is_online'])
            ->each(fn (Rider $rider) => $rider->setAttribute('available', $rider->is_online
                && in_array($rider->id, $onDuty, true)
                && ! in_array($rider->id, $busy, true)));
    }

    /** @return list<int> ids of the shop's riders whose employee has a shift scheduled today */
    public function onDutyIdsForShop(int $shopId): array
    {
        return Rider::query()
            ->where('shop_id', $shopId)
            ->whereHas('employee.schedules', fn ($query) => $query->where('day', now()->format('l')))
            ->pluck('id')
            ->all();
    }

    /** Whether the rider is online and scheduled to work today, so they may be given a run. */
    public function isOnDuty(Rider $rider): bool
    {
        return $rider->is_online && in_array($rider->id, $this->onDutyIdsForShop($rider->shop_id), true);
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
     * A driver who is online with no delivery on the road. The one who has been free the
     * longest (earliest available_since) gets the next order, so the work is shared. Only
     * riders linked to an employee who is scheduled to work today are considered. Meant to be called inside the caller's
     * transaction: the rows are locked while the choice is made.
     */
    public function findAvailableForShop(int $shopId): ?Rider
    {
        return Rider::query()
            ->where('shop_id', $shopId)
            ->active()
            ->whereNotNull('employee_id')
            ->where('is_online', true)
            ->whereHas('employee.schedules', fn ($query) => $query->where('day', now()->format('l')))
            ->whereDoesntHave('deliveries', fn ($query) => $query->whereIn('status', self::BUSY_STATUSES))
            ->orderByRaw('available_since is null')
            ->orderBy('available_since')
            ->orderBy('id')
            ->lockForUpdate()
            ->first();
    }

    public function findByEmployeeId(int $employeeId): ?Rider
    {
        return Rider::query()->where('employee_id', $employeeId)->first();
    }

    /** The active driver record behind a logged-in user, or null when the user is not a driver. */
    public function findActiveForUser(int $userId): ?Rider
    {
        return Rider::query()
            ->active()
            ->whereHas('employee', fn ($query) => $query->where('user_id', $userId))
            ->with('shop')
            ->first();
    }

    public function setOnline(Rider $rider, bool $online): Rider
    {
        $rider->update([
            'is_online' => $online,
            'available_since' => $online ? now() : null,
        ]);

        return $rider;
    }

    /** A finished run puts the driver at the back of the queue of free drivers. */
    public function markFree(Rider $rider): void
    {
        $rider->update(['available_since' => now()]);
    }

    public function listForShop(int $shopId): Collection
    {
        return Rider::query()
            ->where('shop_id', $shopId)
            ->withCount('deliveries')
            ->orderBy('name')
            ->get();
    }

    /** Creates or refreshes the rider that mirrors an employee driver. */
    public function saveForEmployee(int $shopId, int $employeeId, array $attributes): Rider
    {
        return Rider::query()->updateOrCreate(
            ['employee_id' => $employeeId],
            $attributes + ['shop_id' => $shopId],
        );
    }

    public function createForShop(int $shopId, array $data): Rider
    {
        return Rider::query()->create(array_merge($data, ['shop_id' => $shopId]));
    }
}
