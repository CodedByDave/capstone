<?php

namespace App\Observers;

use App\Models\Employee;
use App\Models\EmployeeArchive;

class EmployeeObserver
{
    /**
     * When an employee is soft-deleted, copy their snapshot to employee_archives.
     */
    public function deleted(Employee $employee): void
    {
        if (! $employee->isForceDeleting()) {
            EmployeeArchive::create([
                'shop_id'             => $employee->shop_id,
                'user_id'             => $employee->user_id,
                'employee_id_ref'     => $employee->id,
                'employee_id'         => $employee->employee_id,
                'first_name'          => $employee->first_name,
                'last_name'           => $employee->last_name,
                'phone'               => $employee->phone,
                'address'             => $employee->address,
                'branch_name'         => $employee->branch_name,
                'position'            => $employee->position,
                'hire_date'           => $employee->hire_date,
                'salary'              => $employee->salary,
                'status'              => $employee->status,
                'original_created_at' => $employee->created_at,
                'archived_at'         => now(),
            ]);
        }
    }

    /**
     * When an employee is restored, remove them from employee_archives.
     */
    public function restored(Employee $employee): void
    {
        EmployeeArchive::where('employee_id_ref', $employee->id)->delete();
    }
}
