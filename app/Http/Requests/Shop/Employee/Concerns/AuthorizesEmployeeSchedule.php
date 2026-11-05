<?php

namespace App\Http\Requests\Shop\Employee\Concerns;

use App\Models\Employee;
use App\Models\EmployeeSchedule;
use App\Services\ShopContext;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Only the employee's own shop may change their schedule, and only someone allowed to update
 * employees. An employee or shift of another shop is "not found", like one that does not exist.
 */
trait AuthorizesEmployeeSchedule
{
    public function authorize(): bool
    {
        $employee = $this->route('employee');
        $schedule = $this->route('schedule');

        if (! $employee instanceof Employee
            || $employee->shop_id !== app(ShopContext::class)->shopIdFor($this->user())
            || ($schedule instanceof EmployeeSchedule && $schedule->employee_id !== $employee->id)) {
            throw new NotFoundHttpException;
        }

        return Gate::allows('update', $employee);
    }
}
