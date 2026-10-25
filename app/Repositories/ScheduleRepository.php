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
}
