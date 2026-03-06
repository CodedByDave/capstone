<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeSchedule;
use App\Repositories\ScheduleRepository;
use Illuminate\Support\Facades\DB;

class ScheduleService
{
    public function __construct(
        private readonly ScheduleRepository $scheduleRepository
    ) {}

    public function createSchedule(Employee $employee, array $data)
    {
        return DB::transaction(function () use ($employee, $data) {
            return $this->scheduleRepository->createForEmployee($employee, [
                ...$data,
                'employee_id' => $employee->id
            ]);
        });
    }
}
