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
        ];
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
