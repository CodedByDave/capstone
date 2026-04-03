<?php

namespace App\Http\Middleware;

use App\Models\Employee;
use App\Models\RolePermission;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $module, string $action): mixed
    {
        $user = $request->user();

        if (!$user) return redirect()->route('login');
        if ($user->role === 'owner') return $next($request);

        $employee = Employee::where('user_id', $user->id)->with('roles')->first();

        if (!$employee) {
            return redirect()->route('staff.dashboard')->with('toast', [
                'type'    => 'error',
                'message' => 'No employee record found.',
            ]);
        }

        $roles = $employee->roles->pluck('role')->toArray();

        if (empty($roles)) {
            return redirect()->route('staff.dashboard')->with('toast', [
                'type'    => 'error',
                'message' => 'No roles assigned to your account.',
            ]);
        }

        $hasPermission = RolePermission::where('shop_id', $employee->shop_id)
            ->whereIn('role', $roles)
            ->where('module', $module)
            ->where('action', $action)
            ->exists();

        Log::info('CheckPermission debug', [
            'user_id'        => $user->id,
            'module'         => $module,
            'action'         => $action,
            'roles'          => $roles,
            'shop_id'        => $employee->shop_id,
            'has_permission' => $hasPermission,
        ]);

        if (!$hasPermission) {
            return redirect()->route('staff.dashboard')->with('toast', [
                'type'    => 'error',
                'message' => 'You do not have permission to access this page.',
            ]);
        }

        return $next($request);
    }
}
