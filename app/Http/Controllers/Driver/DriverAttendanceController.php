<?php

namespace App\Http\Controllers\Driver;

use App\Exceptions\DriverAttendanceException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Driver\ClockInRequest;
use App\Http\Requests\Driver\ClockOutRequest;
use App\Services\Driver\DriverAttendanceService;
use Illuminate\Http\RedirectResponse;

/** A driver's own attendance, the same way other staff clock in and out. */
class DriverAttendanceController extends Controller
{
    public function __construct(private readonly DriverAttendanceService $service) {}

    public function clockIn(ClockInRequest $request): RedirectResponse
    {
        try {
            $attendance = $this->service->clockIn(
                $request->attributes->get('rider'),
                (float) $request->validated('latitude'),
                (float) $request->validated('longitude'),
            );
        } catch (DriverAttendanceException $exception) {
            return back()->with('toast', ['type' => 'error', 'message' => $exception->getMessage()]);
        }

        $absent = $attendance->status === 'absent';

        return back()->with('toast', [
            'type' => $absent ? 'error' : 'success',
            'message' => $absent
                ? 'Your location is outside the allowed area. You have been marked absent.'
                : 'Clock-in recorded successfully.',
        ]);
    }

    public function clockOut(ClockOutRequest $request): RedirectResponse
    {
        try {
            $this->service->clockOut(
                $request->attributes->get('rider'),
                $request->filled('latitude') ? (float) $request->validated('latitude') : null,
                $request->filled('longitude') ? (float) $request->validated('longitude') : null,
            );
        } catch (DriverAttendanceException $exception) {
            return back()->with('toast', ['type' => 'error', 'message' => $exception->getMessage()]);
        }

        return back()->with('toast', ['type' => 'success', 'message' => 'Clock-out recorded successfully.']);
    }
}
