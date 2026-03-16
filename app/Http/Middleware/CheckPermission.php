<?php

namespace App\Http\Middleware;

use App\Models\Employee;
use App\Models\RolePermission;
use Closure;
use Illuminate\Http\Request;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $module, string $action): mixed
    {
        $user = $request->user();

        if (!$user) abort(403);

        // Owners always pass
        if ($user->role === 'owner') return $next($request);

        $employee = Employee::where('user_id', $user->id)
            ->with('roles')
            ->first();

        abort_if(!$employee, 403, 'No employee record found.');

        $roles = $employee->roles->pluck('role')->toArray();

        abort_if(empty($roles), 403, 'No roles assigned.');

        // Check if this specific action is permitted
        $hasPermission = RolePermission::where('shop_id', $employee->shop_id)
            ->whereIn('role', $roles)
            ->where('module', $module)
            ->where('action', $action)
            ->exists();

        abort_if(!$hasPermission, 403, 'You do not have permission to perform this action.');

        return $next($request);
    }
}
