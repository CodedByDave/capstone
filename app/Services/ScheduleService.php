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

    public function syncSchedules(Employee $employee, array $schedules): array
    {
        return DB::transaction(function () use ($employee, $schedules) {
            $this->scheduleRepository->deleteAllForEmployee($employee);

            $created = [];
            foreach ($schedules as $schedule) {
                $created[] = $this->scheduleRepository->createForEmployee($employee, [
                    'day'        => $schedule['day'],
                    'start_time' => $schedule['start_time'],
                    'end_time'   => $schedule['end_time'],
                ]);
            }

            return $created;
        });
    }
}
