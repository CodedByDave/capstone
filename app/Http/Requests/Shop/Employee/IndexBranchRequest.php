<?php

namespace App\Http\Requests\Shop\Employee;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexBranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'search' => trim((string) $this->input('search', '')),
            'status' => $this->input('status') === 'all'
                ? null
                : $this->input('status'),
        ]);
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(['Active', 'Inactive'])],
            'sort_by' => [
                'nullable',
                Rule::in([
                    'branch_code',
                    'name',
                    'address',
                    'phone',
                    'email',
                    'manager_name',
                    'employees_count',
                    'opened_at',
                    'status',
                    'creator_name',
                    'updater_name',
                ]),
            ],
            'sort_direction' => ['nullable', Rule::in(['asc', 'desc'])],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', Rule::in([5, 10, 15, 20, 50, 100])],
        ];
    }
}
