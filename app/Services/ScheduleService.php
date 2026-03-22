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

    public function createSchedule(Employee $employee, array $data): EmployeeSchedule
    {
        return DB::transaction(function () use ($employee, $data) {
            return $this->scheduleRepository->createForEmployee($employee, [
                ...$data,
                'employee_id' => $employee->id,
            ]);
        });
    }

    public function updateSchedule(EmployeeSchedule $schedule, array $data): EmployeeSchedule
    {
        return DB::transaction(function () use ($schedule, $data) {
            return $this->scheduleRepository->update($schedule, $data);
        });
    }

    public function deleteSchedule(EmployeeSchedule $schedule): void
    {
        DB::transaction(function () use ($schedule) {
            $this->scheduleRepository->delete($schedule);
        });
    }
}
