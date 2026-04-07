<?php

namespace App\Http\Requests\Shop\Employee;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date'                 => ['required', 'date'],
            'entries'              => ['required', 'array', 'min:1'],
            'entries.*.employee_id' => ['required', 'integer', 'exists:employees,id'],
            'entries.*.status'     => ['required', 'string', 'in:present,absent,late,half_day'],
            'entries.*.time_in'    => ['nullable', 'date_format:H:i'],
            'entries.*.time_out'   => ['nullable', 'date_format:H:i'],
            'entries.*.remarks'    => ['nullable', 'string', 'max:255'],
        ];
    }
}
