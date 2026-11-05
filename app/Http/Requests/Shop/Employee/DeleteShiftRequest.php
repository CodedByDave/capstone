<?php

namespace App\Http\Requests\Shop\Employee;

use App\Http\Requests\Shop\Employee\Concerns\AuthorizesEmployeeSchedule;
use Illuminate\Foundation\Http\FormRequest;

/** Removes one shift; it only needs the shop and permission checks. */
class DeleteShiftRequest extends FormRequest
{
    use AuthorizesEmployeeSchedule;

    public function rules(): array
    {
        return [];
    }
}
