<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\EmployeeSchedule;
use App\Models\Shop;
use App\Repositories\AttendanceRepository;
use App\Repositories\ScheduleRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    public function __construct(
        private readonly AttendanceRepository $attendanceRepository,
        private readonly ActivityLogService $activityLogService,
        private readonly ScheduleRepository $schedules,
    ) {}

    public function getPaginated(Shop $shop, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->attendanceRepository->getPaginated($shop, $filters, $perPage);
    }

    public function getByDate(Shop $shop, string $date, ?int $excludeUserId = null)
    {
        return $this->attendanceRepository->getByDate($shop, $date, $excludeUserId);
    }

    public function getStats(Shop $shop, string $date, ?int $excludeUserId = null): array
    {
        return $this->attendanceRepository->getStats($shop, $date, $excludeUserId);
    }

    public function markAttendance(Shop $shop, array $data): Attendance
    {
        return DB::transaction(function () use ($shop, $data) {
            $attendance = Attendance::updateOrCreate(
                [
                    'employee_id' => $data['employee_id'],
                    'date' => $data['date'],
                ],
                [
                    'shop_id' => $shop->id,
                    'status' => $data['status'],
                    'time_in' => $data['time_in'] ?? null,
                    'time_out' => $data['time_out'] ?? null,
                    'remarks' => $data['remarks'] ?? null,
                    'marked_by' => auth()->id(),
                ]
            );

            $employee = Employee::find($data['employee_id']);

            $this->activityLogService->log(
                subject: $employee,
                action: 'attendance_marked',
                changes: [
                    'date' => ['old' => '', 'new' => $data['date']],
                    'status' => ['old' => '', 'new' => $data['status']],
                ],
                shopId: $shop->id,
                module: 'Attendance',
            );

            return $attendance;
        });
    }

    public function bulkMark(Shop $shop, string $date, array $entries): array
    {
        return DB::transaction(function () use ($shop, $date, $entries) {
            $results = [];

            foreach ($entries as $entry) {
                $results[] = $this->markAttendance($shop, [
                    'employee_id' => $entry['employee_id'],
                    'date' => $date,
                    'status' => $entry['status'],
                    'time_in' => $entry['time_in'] ?? null,
                    'time_out' => $entry['time_out'] ?? null,
                    'remarks' => $entry['remarks'] ?? null,
                ]);
            }

            return $results;
        });
    }

    public function updateAttendance(Attendance $attendance, array $data): Attendance
    {
        return DB::transaction(function () use ($attendance, $data) {
            $oldStatus = $attendance->status;

            $attendance->update([
                'status' => $data['status'],
                'time_in' => $data['time_in'] ?? $attendance->time_in,
                'time_out' => $data['time_out'] ?? $attendance->time_out,
                'remarks' => $data['remarks'] ?? $attendance->remarks,
                'marked_by' => auth()->id(),
            ]);

            $this->activityLogService->log(
                subject: $attendance->employee,
                action: 'attendance_updated',
                changes: [
                    'status' => ['old' => $oldStatus, 'new' => $data['status']],
                ],
                shopId: $attendance->shop_id,
                module: 'Attendance',
            );

            return $attendance->fresh();
        });
    }

    // ── Self clock-in / clock-out (employee-initiated with geolocation) ──────────

    /** Maximum allowed distance in metres from shop to accept a clock-in. */
    private const GEO_RADIUS_METRES = 500;

    /**
     * Employee clocks themselves in.
     *
     * If the shop has GPS coordinates and the employee's location is further than
     * GEO_RADIUS_METRES away, the attendance is recorded as 'absent' automatically.
     *
     * Returns the Attendance record.
     */
    public function selfClockIn(Employee $employee, Shop $shop, ?float $lat, ?float $lng): Attendance
    {
        return DB::transaction(function () use ($employee, $shop, $lat, $lng) {
            // After midnight, an overnight shift still counts on the day it started.
            $workDay = $this->workDayFor($employee)['date']->toDateString();
            $now = now()->format('H:i:s');

            // Geo-validation: auto-absent when out of range
            $withinRange = $this->isWithinRange($shop, $lat, $lng);
            $status = $withinRange ? 'present' : 'absent';
            $remarks = $withinRange
                ? null
                : 'Auto-marked absent: location was outside the allowed range of the shop.';

            $attendance = $this->attendanceRepository->saveForEmployeeOn($employee->id, $workDay, [
                'shop_id' => $shop->id,
                'status' => $status,
                'time_in' => $withinRange ? $now : null,
                'check_in_lat' => $lat,
                'check_in_lng' => $lng,
                'remarks' => $remarks,
                'marked_by' => $employee->user_id,
            ]);

            $this->activityLogService->log(
                subject: $employee,
                action: 'clock_in',
                changes: [
                    'status' => ['old' => '', 'new' => $status],
                    'time_in' => ['old' => '', 'new' => $now],
                ],
                shopId: $shop->id,
                module: 'Attendance',
            );

            return $attendance;
        });
    }

    /**
     * Employee clocks themselves out.
     * Only records time_out; does not change the attendance status.
     */
    public function selfClockOut(Employee $employee, Shop $shop, ?float $lat, ?float $lng): Attendance
    {
        return DB::transaction(function () use ($employee, $shop, $lat, $lng) {
            $now = now()->format('H:i:s');

            // The record of the current work day, or an overnight shift still open from the day before.
            $attendance = $this->todayFor($employee)
                ?? throw (new ModelNotFoundException)->setModel(Attendance::class);

            $attendance->update([
                'time_out' => $now,
                'check_out_lat' => $lat,
                'check_out_lng' => $lng,
                'marked_by' => $employee->user_id,
            ]);

            $this->activityLogService->log(
                subject: $employee,
                action: 'clock_out',
                changes: [
                    'time_out' => ['old' => '', 'new' => $now],
                ],
                shopId: $shop->id,
                module: 'Attendance',
            );

            return $attendance->fresh();
        });
    }

    /**
     * The attendance record of the employee's current work day, or null if none. A shift clocked
     * into the day before and not yet clocked out (an overnight shift that ran late) still counts.
     */
    public function todayFor(Employee $employee): ?Attendance
    {
        $workDay = $this->workDayFor($employee)['date'];
        $record = $this->attendanceRepository->findForEmployeeOn($employee->id, $workDay->toDateString());

        if ($record !== null) {
            return $record;
        }

        $previous = $this->attendanceRepository->findForEmployeeOn($employee->id, $workDay->copy()->subDay()->toDateString());

        return $previous?->time_in !== null && $previous->time_out === null ? $previous : null;
    }

    /**
     * The work day a moment belongs to, with that day's shift. An overnight shift (it ends at or
     * before its start time) that started yesterday still counts on yesterday until it ends.
     *
     * @return array{date: Carbon, shift: ?EmployeeSchedule}
     */
    public function workDayFor(Employee $employee, ?Carbon $at = null): array
    {
        $at = ($at ?? now())->copy();
        $weekly = $this->schedules->forEmployee($employee->id);
        $yesterday = $at->copy()->subDay();
        $overnight = $weekly->firstWhere('day', $yesterday->format('l'));

        if ($overnight?->endsNextDay() && $at->format('H:i') < $overnight->end_time->format('H:i')) {
            return ['date' => $yesterday->startOfDay(), 'shift' => $overnight];
        }

        return ['date' => $at->startOfDay(), 'shift' => $weekly->firstWhere('day', $at->format('l'))];
    }

    /**
     * Returns true if the employee's coordinates are within GEO_RADIUS_METRES of
     * the shop, or if either set of coordinates is missing (geo not configured).
     */
    private function isWithinRange(Shop $shop, ?float $lat, ?float $lng): bool
    {
        if ($shop->latitude === null || $shop->longitude === null) {
            return true;
        }
        if ($lat === null || $lng === null) {
            return false;
        }

        return $this->haversineMetres($shop->latitude, $shop->longitude, $lat, $lng)
            <= self::GEO_RADIUS_METRES;
    }

    /**
     * Haversine formula — returns distance in metres between two GPS coordinates.
     */
    private function haversineMetres(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $R = 6_371_000; // Earth radius in metres
        $φ1 = deg2rad($lat1);
        $φ2 = deg2rad($lat2);
        $Δφ = deg2rad($lat2 - $lat1);
        $Δλ = deg2rad($lon2 - $lon1);

        $a = sin($Δφ / 2) ** 2 + cos($φ1) * cos($φ2) * sin($Δλ / 2) ** 2;

        return $R * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
