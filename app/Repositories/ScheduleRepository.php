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

    public function createForEmployee(Employee $employee, array $data): EmployeeSchedule
    {
        return $employee->schedules()->create($data);
    }

    public function deleteAllForEmployee(Employee $employee): void
    {
        $employee->schedules()->delete();
    }
}
