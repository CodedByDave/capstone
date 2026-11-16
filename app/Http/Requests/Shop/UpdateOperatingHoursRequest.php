<?php

namespace App\Http\Requests\Shop;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOperatingHoursRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isOwner() === true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('operating_days')) {
            $this->merge(['operating_days' => array_values(array_unique(array_map('intval', (array) $this->input('operating_days'))))]);
        }

        $this->merge([
            'is_open_24_7' => $this->boolean('is_open_24_7'),
            'opens_at' => $this->input('opens_at') ?: null,
            'closes_at' => $this->input('closes_at') ?: null,
        ]);
    }

    public function rules(): array
    {
        return [
            'operating_days' => ['sometimes', 'array', 'min:1', 'max:7'],
            'operating_days.*' => ['integer', 'between:0,6'],
            'is_open_24_7' => ['required', 'boolean'],
            'opens_at' => ['nullable', 'required_if:is_open_24_7,false', 'date_format:H:i'],
            'closes_at' => ['nullable', 'required_if:is_open_24_7,false', 'date_format:H:i', 'different:opens_at'],
            'daily_hours' => ['sometimes', 'nullable', 'array'],
            'daily_hours.*.opens_at' => ['required_with:daily_hours', 'date_format:H:i'],
            'daily_hours.*.closes_at' => ['required_with:daily_hours', 'date_format:H:i'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            foreach ((array) $this->input('daily_hours', []) as $day => $hours) {
                if (! in_array((int) $day, range(0, 6), true)) {
                    $validator->errors()->add("daily_hours.$day", 'Invalid day.');
                } elseif (($hours['opens_at'] ?? null) !== null && ($hours['opens_at'] ?? null) === ($hours['closes_at'] ?? null)) {
                    $validator->errors()->add("daily_hours.$day.closes_at", 'Opening and closing time cannot be the same. Choose 24/7 instead.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'operating_days.min' => 'Choose at least one day your shop is open.',
            'opens_at.required_if' => 'Enter the time your shop opens.',
            'closes_at.required_if' => 'Enter the time your shop closes.',
            'closes_at.different' => 'Opening and closing time cannot be the same. Choose 24/7 instead.',
        ];
    }
}
