<?php

namespace App\Http\Requests\Driver;

use Illuminate\Foundation\Http\FormRequest;

/**
 * A driver must share their position to clock in, so the shop's location check can run. A request
 * without one is refused instead of being saved as an absence.
 */
class ClockInRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ];
    }

    public function messages(): array
    {
        return [
            'latitude.required' => 'Turn on your location to clock in.',
            'longitude.required' => 'Turn on your location to clock in.',
        ];
    }
}
