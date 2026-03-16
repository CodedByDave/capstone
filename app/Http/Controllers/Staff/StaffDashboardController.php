<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\RolePermission;
use Inertia\Inertia;

class StaffDashboardController extends Controller
{
    public function index()
    {
        $user     = auth()->user();
        $employee = Employee::where('user_id', $user->id)
            ->with('roles')
            ->firstOrFail();

        $roles = $employee->roles->pluck('role')->toArray();

        // What this staff member can do
        $permissions = RolePermission::where('shop_id', $employee->shop_id)
            ->whereIn('role', $roles)
            ->get(['module', 'action'])
            ->groupBy('module')
            ->map(fn ($items) => $items->pluck('action')->unique()->values());

        return Inertia::render('staff/Dashboard', [
            'employee'    => $employee,
            'permissions' => $permissions,
        ]);
    }
}
