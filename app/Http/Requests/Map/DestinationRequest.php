<?php

namespace App\Http\Requests\Map;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates the `to=lng,lat` query used by the route and fee-quote endpoints.
 */
class DestinationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $parts = array_map('trim', explode(',', (string) $this->query('to', '')));

        $this->merge([
            'dest_lng' => $parts[0] ?? null,
            'dest_lat' => $parts[1] ?? null,
        ]);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'to' => ['required', 'string', 'regex:/^-?\d+(\.\d+)?,-?\d+(\.\d+)?$/'],
            'dest_lng' => ['required', 'numeric', 'between:-180,180'],
            'dest_lat' => ['required', 'numeric', 'between:-90,90'],
        ];
    }

    public function messages(): array
    {
        return ['to.regex' => 'The destination must look like "lng,lat".'];
    }

    public function destinationLat(): float
    {
        return (float) $this->validated('dest_lat');
    }

    public function destinationLng(): float
    {
        return (float) $this->validated('dest_lng');
    }
}
