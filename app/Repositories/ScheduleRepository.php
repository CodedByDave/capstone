<?php

namespace App\Repositories;

use App\Models\Employee;
use App\Models\EmployeeSchedule;

class ScheduleRepository extends Repository
{
    public function __construct(EmployeeSchedule $employeeSchedule)
    {
        parent::__construct($employeeSchedule);
    }

    /** @return \Illuminate\Database\Eloquent\Collection<int, EmployeeSchedule> the employee's weekly shifts */
    public function forEmployee(int $employeeId): \Illuminate\Database\Eloquent\Collection
    {
        return EmployeeSchedule::query()->where('employee_id', $employeeId)->get();
    }

    public function createForEmployee(Employee $employee, array $data): EmployeeSchedule
    {
        return $employee->schedules()->create($data);
    }

    public function deleteAllForEmployee(Employee $employee): void
    {
        $employee->schedules()->delete();
    }

    public function existsForDay(Employee $employee, string $day): bool
    {
        return $employee->schedules()->where('day', $day)->exists();
    }

    public function updateSchedule(EmployeeSchedule $schedule, array $data): EmployeeSchedule
    {
        $schedule->update($data);

        return $schedule->refresh();
    }

    public function deleteSchedule(EmployeeSchedule $schedule): void
    {
        $schedule->delete();
    }
}
