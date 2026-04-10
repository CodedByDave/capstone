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
use App\Traits\BranchScoped;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AttendanceController extends Controller
{
    use BranchScoped;
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
        $shop        = $this->getShop();
        $date        = $request->get('date', now()->toDateString());
        $staffBranch = $this->getStaffBranch(); // null for owners

        $query = Employee::where('shop_id', $shop->id)
            ->where('status', 'Active')
            ->orderBy('last_name');

        // Branch isolation: staff can only see their own branch, excluding themselves
        if ($staffBranch !== null) {
            $query->where('branch_name', $staffBranch)
                  ->where(function ($q) {
                      $q->where('user_id', '!=', auth()->id())
                        ->orWhereNull('user_id');
                  });
        } elseif ($request->filled('branch')) {
            // Owner can filter by branch via query param
            $branch = $request->get('branch');
            if ($branch === '__none__') {
                $query->whereNull('branch_name')->orWhere('branch_name', '');
            } else {
                $query->where('branch_name', $branch);
            }
        }

        $employees   = $query->get(['id', 'employee_id', 'first_name', 'last_name', 'position', 'branch_name']);

        $excludeUserId = $staffBranch !== null ? auth()->id() : null;
        $attendances   = $this->attendanceService->getByDate($shop, $date, $staffBranch, $excludeUserId);

        // For owners, show all branches; staff see only their own branch
        $branches = $staffBranch !== null
            ? collect([$staffBranch])
            : Employee::where('shop_id', $shop->id)
                ->whereNotNull('branch_name')
                ->where('branch_name', '!=', '')
                ->distinct()
                ->pluck('branch_name')
                ->sort()
                ->values();

        // Load schedules for all active employees so the frontend
        $schedules = EmployeeSchedule::whereIn('employee_id', $employees->pluck('id'))
            ->get(['employee_id', 'day', 'start_time', 'end_time']);

        return Inertia::render('shop/employee/attendance/Index', [
            'employees'   => $employees,
            'attendances' => $attendances,
            'stats'       => $this->attendanceService->getStats($shop, $date, $staffBranch, $excludeUserId),
            'date'        => $date,
            'branches'    => $branches,
            'filters'     => $request->only(['date', 'status', 'search', 'branch']),
            'schedules'   => $schedules,
        ]);
    }

    public function store(AttendanceRequest $request)
    {
        $shop = $this->getShop();
        $data = $request->validated();

        $this->attendanceService->bulkMark($shop, $data['date'], $data['entries']);

        return back()->with('toast', [
            'type'    => 'success',
            'message' => 'Attendance saved successfully.',
        ]);
    }

    public function update(UpdateAttendanceRequest $request, Attendance $attendance)
    {
        $this->attendanceService->updateAttendance($attendance, $request->validated());

        return back()->with('toast', [
            'type'    => 'success',
            'message' => 'Attendance updated.',
        ]);
    }
}
