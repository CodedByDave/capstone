<?php

namespace App\Http\Requests\Shop\Employee;

use App\Http\Requests\Shop\Employee\Concerns\AuthorizesEmployeeSchedule;
use Illuminate\Foundation\Http\FormRequest;

/** The employee's whole weekly schedule: at most one shift per day. */
class ScheduleRequest extends FormRequest
{
    use AuthorizesEmployeeSchedule;

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // An empty list clears the schedule, so every day is a day off.
            'schedules' => ['present', 'array', 'max:7'],
            'schedules.*.day' => ['required', 'string', 'distinct', 'in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday'],
            'schedules.*.start_time' => ['required', 'date_format:H:i'],
            // An end time at or before the start time is an overnight shift that ends the next day.
            'schedules.*.end_time' => ['required', 'date_format:H:i', 'different:schedules.*.start_time'],
        ];
    }

    public function messages(): array
    {
        return [
            'schedules.*.day.distinct' => 'Each day can have only one shift.',
            'schedules.*.start_time.required' => 'Start time is required.',
            'schedules.*.end_time.required' => 'End time is required.',
            'schedules.*.end_time.different' => 'A shift cannot start and end at the same time.',
        ];
    }
}
