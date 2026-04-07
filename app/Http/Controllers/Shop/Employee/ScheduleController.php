<?php

namespace App\Http\Controllers\Shop\Employee;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeSchedule;
use App\Services\ScheduleService;
use App\Http\Requests\Shop\Employee\ScheduleRequest;
use Inertia\Inertia;

class ScheduleController extends Controller
{
    public function __construct(
        private readonly ScheduleService $scheduleService
    ) {}

    public function create(Employee $employee)
    {
        return Inertia::render('shop/employee/schedule/Create', [
            'employee' => $employee,
        ]);
    }

    public function sync(ScheduleRequest $request, Employee $employee)
    {
        $schedules = $this->scheduleService->syncSchedules($employee, $request->validated('schedules'));

        return response()->json($schedules, 200);
    }

    public function update(ScheduleRequest $request, Employee $employee, EmployeeSchedule $schedule)
    {
        $updated = $this->scheduleService->updateSchedule($schedule, $request->validated());

        return response()->json(
            $updated->only(['id', 'work_date', 'start_time', 'end_time'])
        );
    }

    public function destroy(Employee $employee, EmployeeSchedule $schedule)
    {
        $this->scheduleService->deleteSchedule($schedule);

        return response()->json(['success' => true]);
    }
}
