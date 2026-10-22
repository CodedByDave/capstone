<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlatformRatingFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() === true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'per_page' => min(max($this->integer('per_page', 15), 5), 100),
        ]);
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'stars' => ['nullable', 'integer', 'between:1,5'],
            'sort_by' => ['nullable', Rule::in(['shop', 'owner', 'rating', 'created_at'])],
            'sort_direction' => ['nullable', Rule::in(['asc', 'desc'])],
            'per_page' => ['required', 'integer', 'between:5,100'],
        ];
    }

    /** @return array<string, mixed> */
    public function filters(): array
    {
        return $this->validated() + ['sort_by' => 'created_at', 'sort_direction' => 'desc'];
    }
}
