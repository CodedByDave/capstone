<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\RolePermission;
use App\Models\User;

class EmployeePolicy
{
    /** Owners always pass; staff pass when they hold the required HRM permission. */
    private function staffCan(User $user, string $action): bool
    {
        if ($user->role === 'owner') return true;
        if ($user->role !== 'staff') return false;

        $employee = Employee::where('user_id', $user->id)->with('roles')->first();
        if (! $employee) return false;

        $roles = $employee->roles->pluck('role')->toArray();
        if (empty($roles)) return false;

        return RolePermission::where('shop_id', $employee->shop_id)
            ->whereIn('role', $roles)
            ->where('module', 'HRM')
            ->where('action', $action)
            ->exists();
    }

    public function view(User $user, Employee $employee): bool
    {
        return $this->staffCan($user, 'view');
    }

    public function update(User $user, Employee $employee): bool
    {
        return $this->staffCan($user, 'update');
    }

    public function delete(User $user, Employee $employee): bool
    {
        return $this->staffCan($user, 'archive');
    }

    public function restore(User $user, Employee $employee): bool
    {
        return $this->staffCan($user, 'archive');
    }
}
