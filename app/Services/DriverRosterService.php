<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Rider;
use App\Repositories\RiderRepository;

/**
 * Keeps the shop's delivery riders in step with its employees: an active employee whose
 * position is "Driver" is a rider; anyone else is not.
 */
class DriverRosterService
{
    public const DRIVER_POSITION = 'driver';

    public function __construct(private readonly RiderRepository $riders) {}

    public static function isDriverPosition(?string $position): bool
    {
        return mb_strtolower(trim((string) $position)) === self::DRIVER_POSITION;
    }

    /** Creates, updates or deactivates the rider that mirrors the employee. */
    public function sync(Employee $employee): ?Rider
    {
        $rider = $this->riders->findByEmployeeId($employee->id);

        $isDriver = ! $employee->trashed()
            && $employee->status === 'Active'
            && self::isDriverPosition($employee->position);

        if (! $isDriver) {
            if ($rider) {
                $rider->update(['status' => 'inactive', 'is_online' => false, 'available_since' => null]);
            }

            return $rider;
        }

        return $this->riders->saveForEmployee($employee->shop_id, $employee->id, [
            'name' => trim($employee->first_name.' '.$employee->last_name),
            'phone' => $employee->phone,
            'status' => 'active',
        ]);
    }
}
