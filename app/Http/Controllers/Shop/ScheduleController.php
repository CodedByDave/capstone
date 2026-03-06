<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Services\ScheduleService;
use App\Http\Requests\Shop\ScheduleRequest;
use Illuminate\Http\Request;
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
        if ($employee->schedule) {
            return redirect()->back()
                ->with('toast', [
                    'type'    => 'error',
                    'message' => 'Employee already has a schedule.'
                ]);
        }

        $this->scheduleService->createSchedule($employee, $request->validated());

        return redirect()->route('employee.show', $employee->id)
            ->with('toast', [
                'type'    => 'success',
                'message' => 'Schedule created successfully.'
            ]);
    }
}
