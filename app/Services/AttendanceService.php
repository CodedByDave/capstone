<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Shop;
use App\Repositories\AttendanceRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AttendanceService
{
    public function __construct(
        private readonly AttendanceRepository $attendanceRepository,
        private readonly ActivityLogService $activityLogService,
    ) {}

    public function getPaginated(Shop $shop, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->attendanceRepository->getPaginated($shop, $filters, $perPage);
    }

    public function getByDate(Shop $shop, string $date)
    {
        return $this->attendanceRepository->getByDate($shop, $date);
    }

    public function getStats(Shop $shop, string $date): array
    {
        return $this->attendanceRepository->getStats($shop, $date);
    }

    public function markAttendance(Shop $shop, array $data): Attendance
    {
        return DB::transaction(function () use ($shop, $data) {
            $attendance = Attendance::updateOrCreate(
                [
                    'employee_id' => $data['employee_id'],
                    'date'        => $data['date'],
                ],
                [
                    'shop_id'   => $shop->id,
                    'status'    => $data['status'],
                    'time_in'   => $data['time_in'] ?? null,
                    'time_out'  => $data['time_out'] ?? null,
                    'remarks'   => $data['remarks'] ?? null,
                    'marked_by' => auth()->id(),
                ]
            );

            $employee = Employee::find($data['employee_id']);

            $this->activityLogService->log(
                subject: $employee,
                action: 'attendance_marked',
                changes: [
                    'date'   => ['old' => '', 'new' => $data['date']],
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
                    'date'        => $date,
                    'status'      => $entry['status'],
                    'time_in'     => $entry['time_in'] ?? null,
                    'time_out'    => $entry['time_out'] ?? null,
                    'remarks'     => $entry['remarks'] ?? null,
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
                'status'    => $data['status'],
                'time_in'   => $data['time_in'] ?? $attendance->time_in,
                'time_out'  => $data['time_out'] ?? $attendance->time_out,
                'remarks'   => $data['remarks'] ?? $attendance->remarks,
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
}
