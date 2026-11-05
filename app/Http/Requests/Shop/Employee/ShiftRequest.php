<?php

namespace App\Http\Requests\Shop\Employee;

use App\Http\Requests\Shop\Employee\Concerns\AuthorizesEmployeeSchedule;
use Illuminate\Foundation\Http\FormRequest;

/** One shift of an employee's week. */
class ShiftRequest extends FormRequest
{
    use AuthorizesEmployeeSchedule;

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'day' => ['required', 'string', 'in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday'],
            'start_time' => ['required', 'date_format:H:i'],
            // An end time at or before the start time is an overnight shift that ends the next day.
            'end_time' => ['required', 'date_format:H:i', 'different:start_time'],
        ];
    }

    public function messages(): array
    {
        return [
            'start_time.required' => 'Start time is required.',
            'end_time.required' => 'End time is required.',
            'end_time.different' => 'A shift cannot start and end at the same time.',
        ];
    }
}
