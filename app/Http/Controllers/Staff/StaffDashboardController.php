<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\EmployeeSchedule;
use App\Models\RolePermission;
use App\Models\Shop;
use App\Services\AttendanceService;
use Inertia\Inertia;

class StaffDashboardController extends Controller
{
    public function __construct(
        private readonly AttendanceService $attendanceService,
    ) {}

    public function index()
    {
        $user     = auth()->user();
        $employee = Employee::where('user_id', $user->id)
            ->with('roles')
            ->firstOrFail();

        $roles = $employee->roles->pluck('role')->toArray();

        // What modules this staff member can access
        $permissions = RolePermission::where('shop_id', $employee->shop_id)
            ->whereIn('role', $roles)
            ->get(['module', 'action'])
            ->groupBy('module')
            ->map(fn ($items) => $items->pluck('action')->unique()->values());

        // Today's attendance record (for the clock-in/out widget)
        $today = $this->attendanceService->todayFor($employee);
        $shop  = Shop::find($employee->shop_id);

        // Employee's work schedules (day + shift times)
        $schedules = EmployeeSchedule::where('employee_id', $employee->id)
            ->get(['day', 'start_time', 'end_time'])
            ->map(fn ($s) => [
                'day'        => $s->day,
                'start_time' => $s->start_time ? $s->start_time->format('H:i') : null,
                'end_time'   => $s->end_time   ? $s->end_time->format('H:i')   : null,
            ]);

        // Last 30 days of this employee's own attendance records
        $ownAttendances = Attendance::where('employee_id', $employee->id)
            ->where('date', '>=', now()->subDays(29)->toDateString())
            ->orderByDesc('date')
            ->get(['date', 'status', 'time_in', 'time_out', 'remarks'])
            ->map(fn ($a) => [
                'date'     => $a->date,
                'status'   => $a->status,
                'time_in'  => $a->time_in  ? \Carbon\Carbon::parse($a->time_in)->format('H:i')  : null,
                'time_out' => $a->time_out ? \Carbon\Carbon::parse($a->time_out)->format('H:i') : null,
                'remarks'  => $a->remarks,
            ]);

        return Inertia::render('staff/Dashboard', [
            'employee' => [
                'id'          => $employee->id,
                'first_name'  => $employee->first_name,
                'last_name'   => $employee->last_name,
                'position'    => $employee->position,
                'branch_name' => $employee->branch_name,
            ],
            'permissions' => $permissions,
            'today_attendance' => $today ? [
                'status'   => $today->status,
                'time_in'  => $today->time_in,
                'time_out' => $today->time_out,
                'remarks'  => $today->remarks,
            ] : null,
            'shop_has_geo'    => $shop && $shop->latitude !== null && $shop->longitude !== null,
            'schedules'       => $schedules,
            'own_attendances' => $ownAttendances,
        ]);
    }
}
