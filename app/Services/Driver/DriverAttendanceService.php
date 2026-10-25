<?php

namespace App\Services\Driver;

use App\Exceptions\DriverAttendanceException;
use App\Models\Attendance;
use App\Models\Rider;
use App\Repositories\AttendanceRepository;
use App\Repositories\ScheduleRepository;
use App\Services\AttendanceService;

/**
 * A driver clocks in and out like any other staff member: only on a day in their weekly
 * schedule, once a day, and from where the shop's location check allows.
 */
class DriverAttendanceService
{
    /** Days of attendance history shown in the app. */
    private const HISTORY_DAYS = 14;

    public function __construct(
        private readonly AttendanceService $attendance,
        private readonly ScheduleRepository $schedules,
        private readonly AttendanceRepository $records,
    ) {}

    /** What the attendance card shows today, including why the buttons may be disabled. */
    public function state(Rider $rider): array
    {
        $employee = $rider->employee;
        $day = now()->format('l');

        $weekly = $this->schedules->forEmployee($employee->id);
        $shift = $weekly->firstWhere('day', $day);
        $record = $this->attendance->todayFor($employee);

        $clockedIn = $record?->time_in !== null;
        $clockedOut = $record?->time_out !== null;

        return [
            'day' => $day,
            'has_schedule' => $weekly->isNotEmpty(),
            'is_work_day' => $shift !== null,
            'shift' => $shift ? [
                'start' => $shift->start_time?->format('H:i'),
                'end' => $shift->end_time?->format('H:i'),
            ] : null,
            'record' => $record ? [
                'status' => $record->status,
                'time_in' => $record->time_in,
                'time_out' => $record->time_out,
                'remarks' => $record->remarks,
            ] : null,
            'has_clocked_in' => $clockedIn,
            'has_clocked_out' => $clockedOut,
            'blocked_reason' => $this->blockedReason($weekly->isNotEmpty(), $shift !== null, $day),
        ];
    }

    /** @return list<array<string, mixed>> the driver's own attendance for the last two weeks, newest first */
    public function history(Rider $rider): array
    {
        return $this->records
            ->recentForEmployee($rider->employee_id, now()->subDays(self::HISTORY_DAYS - 1))
            ->map(fn (Attendance $record) => [
                'date' => $record->date instanceof \DateTimeInterface ? $record->date->format('Y-m-d') : (string) $record->date,
                'status' => $record->status,
                'time_in' => $record->time_in,
                'time_out' => $record->time_out,
                'remarks' => $record->remarks,
            ])
            ->all();
    }

    /**
     * @throws DriverAttendanceException when the driver has no shift today or already clocked in
     */
    public function clockIn(Rider $rider, float $lat, float $lng): Attendance
    {
        $employee = $rider->employee;
        $state = $this->state($rider);

        if ($state['blocked_reason'] !== null) {
            throw new DriverAttendanceException($state['blocked_reason']);
        }

        if ($state['has_clocked_in']) {
            throw new DriverAttendanceException('You have already clocked in today.');
        }

        return $this->attendance->selfClockIn($employee, $rider->shop, $lat, $lng);
    }

    /**
     * @throws DriverAttendanceException when the driver has not clocked in or already clocked out
     */
    public function clockOut(Rider $rider, ?float $lat, ?float $lng): Attendance
    {
        $state = $this->state($rider);

        if (! $state['has_clocked_in']) {
            throw new DriverAttendanceException('You have not clocked in yet today.');
        }

        if ($state['has_clocked_out']) {
            throw new DriverAttendanceException('You have already clocked out today.');
        }

        return $this->attendance->selfClockOut($rider->employee, $rider->shop, $lat, $lng);
    }

    private function blockedReason(bool $hasSchedule, bool $isWorkDay, string $day): ?string
    {
        return match (true) {
            ! $hasSchedule => "Your work schedule hasn't been set up yet. Ask the shop owner to set it.",
            ! $isWorkDay => "{$day} is not in your work schedule.",
            default => null,
        };
    }
}
