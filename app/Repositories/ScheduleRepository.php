<?php

namespace App\Repositories;

use App\Models\Employee;
use App\Models\EmployeeSchedule;

use Illuminate\Database\Eloquent\Model;

class ScheduleRepository extends Repository
{
    public function __construct(EmployeeSchedule $employeeSchedule)
    {
        return parent::__construct($employeeSchedule);
    }

    public function createForEmployee(Employee $employee, array $data)
    {
        return $employee->schedule()->create($data);
    }
}
