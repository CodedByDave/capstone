<?php

namespace App\Repositories;

use App\Models\Delivery;
use App\Models\Rider;
use App\Models\Shop;
use Illuminate\Database\Eloquent\Builder;
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
            ->each(function (Rider $rider) use ($busy, $onDuty): void {
                // Why the rider cannot take a run right now, so staff are not told "on delivery" for an offline driver.
                $reason = match (true) {
                    in_array($rider->id, $busy, true) => 'On delivery',
                    ! in_array($rider->id, $onDuty, true) => 'Not clocked in',
                    ! $rider->is_online => 'Offline',
                    default => null,
                };

                $rider->setAttribute('available', $reason === null);
                $rider->setAttribute('unavailable_reason', $reason);
            });
    }

    /**
     * Narrows a rider query to those who are clocked in right now: a time-in on today's record (or
     * yesterday's, for an overnight shift) and no time-out yet. A rider who has not clocked in, or
     * was marked absent for being outside the allowed area, receives no orders.
     */
    private function whereClockedIn(Builder $query): Builder
    {
        return $query->whereHas('employee.attendances', fn ($attendance) => $attendance
            ->whereIn('date', [now()->toDateString(), now()->subDay()->toDateString()])
            ->whereNotNull('time_in')
            ->whereNull('time_out'));
    }

    /** @return list<int> ids of the shop's riders scheduled to work today who have clocked in */
    public function onDutyIdsForShop(int $shopId): array
    {
        return $this->whereClockedIn(Rider::query()->where('shop_id', $shopId))
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
    /** @param  list<int>  $excluding  riders who must not get this run, such as ones who turned it down */
    /** @param  list<string>|null  $vehicleTypes  only riders driving one of these vehicles; null means any */
    public function findAvailableForShop(int $shopId, array $excluding = [], ?array $vehicleTypes = null): ?Rider
    {
        return $this->whereClockedIn(Rider::query())
            ->where('shop_id', $shopId)
            ->whereNotIn('id', $excluding)
            ->when($vehicleTypes !== null, fn (Builder $query) => $query->where(function (Builder $query) use ($vehicleTypes): void {
                $query->whereIn('vehicle_type', $vehicleTypes);

                // A rider with no vehicle type is treated as driving the default one.
                if (in_array(Shop::DEFAULT_RIDER_VEHICLE_TYPE, $vehicleTypes, true)) {
                    $query->orWhereNull('vehicle_type')->orWhere('vehicle_type', '');
                }
            }))
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

    /**
     * Whether the shop has any rider who could take a run: active, online and scheduled to work
     * today. A rider who is on a run counts, since they free up; only a shop with nobody on duty
     * has no rider available.
     */
    public function hasAvailableForShop(int $shopId): bool
    {
        return $this->whereClockedIn(Rider::query())
            ->where('shop_id', $shopId)
            ->active()
            ->whereNotNull('employee_id')
            ->where('is_online', true)
            ->whereHas('employee.schedules', fn ($query) => $query->where('day', now()->format('l')))
            ->exists();
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

    /**
     * The vehicle types of the riders who could take a run right now: active, online, scheduled
     * today and clocked in. Used so what customers see matches who can actually come.
     *
     * @return list<string>
     */
    public function onDutyVehicleTypes(int $shopId): array
    {
        return $this->whereClockedIn(Rider::query())
            ->where('shop_id', $shopId)
            ->active()
            ->whereNotNull('employee_id')
            ->where('is_online', true)
            ->whereHas('employee.schedules', fn ($query) => $query->where('day', now()->format('l')))
            ->pluck('vehicle_type')
            ->map(fn ($type) => filled($type) ? (string) $type : Shop::DEFAULT_RIDER_VEHICLE_TYPE)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * The vehicle types the shop's active riders drive. A rider with no vehicle type counts as
     * the default type, so every rider is represented.
     *
     * @return list<string>
     */
    public function activeVehicleTypes(int $shopId): array
    {
        return Rider::query()
            ->where('shop_id', $shopId)
            ->active()
            ->whereNotNull('employee_id')
            ->pluck('vehicle_type')
            ->map(fn ($type) => filled($type) ? (string) $type : Shop::DEFAULT_RIDER_VEHICLE_TYPE)
            ->unique()
            ->values()
            ->all();
    }

    public function createForShop(int $shopId, array $data): Rider
    {
        return Rider::query()->create(array_merge($data, ['shop_id' => $shopId]));
    }
}
