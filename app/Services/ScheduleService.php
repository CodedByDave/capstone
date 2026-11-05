<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeSchedule;
use App\Repositories\ScheduleRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * An employee's weekly shifts: one shift per weekday. A shift whose end time is at or before its
 * start time is an overnight shift; it ends the next day but still belongs to the day it started.
 */
class ScheduleService
{
    public function __construct(
        private readonly ScheduleRepository $scheduleRepository
    ) {}

    /** Replaces the employee's whole weekly schedule. */
    public function syncSchedules(Employee $employee, array $schedules): array
    {
        return DB::transaction(function () use ($employee, $schedules) {
            $this->scheduleRepository->deleteAllForEmployee($employee);

            $created = [];
            foreach ($schedules as $schedule) {
                $created[] = $this->scheduleRepository->createForEmployee($employee, $this->shiftData($schedule));
            }

            return $created;
        });
    }

    /**
     * Adds one shift to the employee's week.
     *
     * @throws ValidationException when the employee already has a shift on that day
     */
    public function addSchedule(Employee $employee, array $data): EmployeeSchedule
    {
        return DB::transaction(function () use ($employee, $data) {
            if ($this->scheduleRepository->existsForDay($employee, $data['day'])) {
                throw ValidationException::withMessages([
                    'day' => "{$employee->first_name} already has a shift on {$data['day']}. Edit that shift instead.",
                ]);
            }

            return $this->scheduleRepository->createForEmployee($employee, $this->shiftData($data));
        });
    }

    /**
     * Changes one shift.
     *
     * @throws ValidationException when the shift is moved to a day that already has one
     */
    public function updateSchedule(EmployeeSchedule $schedule, array $data): EmployeeSchedule
    {
        return DB::transaction(function () use ($schedule, $data) {
            if ($data['day'] !== $schedule->day
                && $this->scheduleRepository->existsForDay($schedule->employee, $data['day'])) {
                throw ValidationException::withMessages([
                    'day' => "There is already a shift on {$data['day']}.",
                ]);
            }

            return $this->scheduleRepository->updateSchedule($schedule, $this->shiftData($data));
        });
    }

    public function deleteSchedule(EmployeeSchedule $schedule): void
    {
        $this->scheduleRepository->deleteSchedule($schedule);
    }

    /** @return array{day: string, start_time: string, end_time: string} */
    private function shiftData(array $schedule): array
    {
        return [
            'day' => $schedule['day'],
            'start_time' => $schedule['start_time'],
            'end_time' => $schedule['end_time'],
        ];
    }
}
