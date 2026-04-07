<?php

namespace App\Http\Requests\Shop\Employee;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status'   => ['required', 'string', 'in:present,absent,late,half_day'],
            'time_in'  => ['nullable', 'date_format:H:i'],
            'time_out' => ['nullable', 'date_format:H:i'],
            'remarks'  => ['nullable', 'string', 'max:255'],
        ];
    }
}
