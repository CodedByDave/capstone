<?php

namespace App\Http\Controllers\Shop\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\Employee\DeleteShiftRequest;
use App\Http\Requests\Shop\Employee\ScheduleRequest;
use App\Http\Requests\Shop\Employee\ShiftRequest;
use App\Models\Employee;
use App\Models\EmployeeSchedule;
use App\Services\ScheduleService;
use Illuminate\Http\JsonResponse;

/** An employee's weekly shifts. Access to the employee and shift is checked by each Form Request. */
class ScheduleController extends Controller
{
    public function __construct(
        private readonly ScheduleService $scheduleService
    ) {}

    /** Replaces the whole week. */
    public function sync(ScheduleRequest $request, Employee $employee): JsonResponse
    {
        return response()->json($this->scheduleService->syncSchedules($employee, $request->validated('schedules')));
    }

    public function store(ShiftRequest $request, Employee $employee): JsonResponse
    {
        return response()->json($this->scheduleService->addSchedule($employee, $request->validated()), 201);
    }

    public function update(ShiftRequest $request, Employee $employee, EmployeeSchedule $schedule): JsonResponse
    {
        $updated = $this->scheduleService->updateSchedule($schedule, $request->validated());

        return response()->json($updated->only(['id', 'day', 'start_time', 'end_time']));
    }

    public function destroy(DeleteShiftRequest $request, Employee $employee, EmployeeSchedule $schedule): JsonResponse
    {
        $this->scheduleService->deleteSchedule($schedule);

        return response()->json(['success' => true]);
    }
}
