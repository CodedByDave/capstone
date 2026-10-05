<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AuditLogFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'per_page' => min(max($this->integer('per_page', 20), 5), 100),
        ]);
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', Rule::in(['authentication', 'activity'])],
            'role' => ['nullable', 'string', 'max:100'],
            'module' => ['nullable', 'string', 'max:255'],
            'event' => ['nullable', 'string', 'max:255'],
            'date' => ['nullable', 'date_format:Y-m-d'],
            'sort_by' => ['nullable', Rule::in([
                'name', 'email', 'category', 'module', 'event', 'occurred_at',
            ])],
            'sort_direction' => ['nullable', Rule::in(['asc', 'desc'])],
            'per_page' => ['required', 'integer', 'between:5,100'],
        ];
    }

    public function filters(): array
    {
        $filters = $this->validated();
        $filters['per_page'] = (string) $filters['per_page'];

        return $filters;
    }
}
