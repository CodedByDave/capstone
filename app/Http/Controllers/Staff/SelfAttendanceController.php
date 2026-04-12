<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Shop;
use App\Services\AttendanceService;
use Illuminate\Http\Request;

class SelfAttendanceController extends Controller
{
    public function __construct(
        private readonly AttendanceService $attendanceService,
    ) {}

    public function clockIn(Request $request)
    {
        $request->validate([
            'latitude'  => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $employee = Employee::where('user_id', auth()->id())->firstOrFail();
        $shop     = Shop::findOrFail($employee->shop_id);

        // Prevent double clock-in on the same day
        $existing = $this->attendanceService->todayFor($employee);
        if ($existing && $existing->time_in !== null) {
            return back()->with('toast', [
                'type'    => 'error',
                'message' => 'You have already clocked in today.',
            ]);
        }

        $attendance = $this->attendanceService->selfClockIn(
            $employee,
            $shop,
            $request->latitude  ? (float) $request->latitude  : null,
            $request->longitude ? (float) $request->longitude : null,
        );

        $message = $attendance->status === 'absent'
            ? 'Your location is outside the allowed area. You have been marked absent.'
            : 'Clock-in recorded successfully.';

        return back()->with('toast', [
            'type'    => $attendance->status === 'absent' ? 'error' : 'success',
            'message' => $message,
        ]);
    }

    public function clockOut(Request $request)
    {
        $request->validate([
            'latitude'  => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $employee = Employee::where('user_id', auth()->id())->firstOrFail();
        $shop     = Shop::findOrFail($employee->shop_id);

        $existing = $this->attendanceService->todayFor($employee);

        if (!$existing || $existing->time_in === null) {
            return back()->with('toast', [
                'type'    => 'error',
                'message' => 'You have not clocked in yet today.',
            ]);
        }

        if ($existing->time_out !== null) {
            return back()->with('toast', [
                'type'    => 'error',
                'message' => 'You have already clocked out today.',
            ]);
        }

        $this->attendanceService->selfClockOut(
            $employee,
            $shop,
            $request->latitude  ? (float) $request->latitude  : null,
            $request->longitude ? (float) $request->longitude : null,
        );

        return back()->with('toast', [
            'type'    => 'success',
            'message' => 'Clock-out recorded successfully.',
        ]);
    }
}
