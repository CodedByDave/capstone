<?php

namespace App\Http\Controllers\Shop\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\Employee\AttendanceRequest;
use App\Http\Requests\Shop\Employee\UpdateAttendanceRequest;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\EmployeeSchedule;
use App\Models\Shop;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AttendanceController extends Controller
{
    public function __construct(
        private readonly AttendanceService $attendanceService,
    ) {}

    private function getShop(): Shop
    {
        $user = auth()->user();

        if ($user->role === 'owner') {
            return Shop::where('owner_id', $user->id)->firstOrFail();
        }

        $employee = Employee::where('user_id', $user->id)->firstOrFail();

        return Shop::findOrFail($employee->shop_id);
    }

    public function index(Request $request)
    {
        $shop = $this->getShop();
        $date = $request->get('date', now()->toDateString());
        $query = Employee::where('shop_id', $shop->id)
            ->where('status', 'Active')
            ->orderBy('last_name');

        $employees = $query->get(['id', 'employee_id', 'first_name', 'last_name', 'position']);

        $attendances = $this->attendanceService->getByDate($shop, $date);

        // Load schedules for all active employees so the frontend
        $schedules = EmployeeSchedule::whereIn('employee_id', $employees->pluck('id'))
            ->get(['employee_id', 'day', 'start_time', 'end_time']);

        return Inertia::render('shop/employee/attendance/Index', [
            'employees' => $employees,
            'attendances' => $attendances,
            'stats' => $this->attendanceService->getStats($shop, $date),
            'date' => $date,
            'filters' => $request->only(['date', 'status', 'search']),
            'schedules' => $schedules,
            'can_edit' => auth()->user()->role === 'owner',
        ]);
    }

    public function store(AttendanceRequest $request)
    {
        $shop = $this->getShop();
        $data = $request->validated();

        $this->attendanceService->bulkMark($shop, $data['date'], $data['entries']);

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Attendance saved successfully.',
        ]);
    }

    public function update(UpdateAttendanceRequest $request, Attendance $attendance)
    {
        $this->attendanceService->updateAttendance($attendance, $request->validated());

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Attendance updated.',
        ]);
    }
}
