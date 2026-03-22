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

    public function store(ScheduleRequest $request, Employee $employee)
    {
        $schedule = $this->scheduleService->createSchedule($employee, $request->validated());

        return response()->json(
            $schedule->only(['id', 'work_date', 'start_time', 'end_time']),
            201
        );
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
