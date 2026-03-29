<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Employee;

class EmployeePolicy
{
    public function view(User $user, Employee $employee): bool
    {
        if ($user->role === 'owner') return true;
        if ($user->role === 'manager') {
            return $user->employee->branch_name === $employee->branch_name;
        }
        return $user->id === $employee->user_id;
    }

    public function update(User $user, Employee $employee): bool
    {
        if ($user->role === 'owner') return true;
        if ($user->role === 'manager') {
            return $user->employee->branch_name === $employee->branch_name;
        }
        return $user->id === $employee->user_id;
    }

    public function delete(User $user, Employee $employee): bool
    {
        return $user->role === 'owner';
    }

    public function restore(User $user, Employee $employee): bool
    {
        return $user->role === 'owner';
    }
}
