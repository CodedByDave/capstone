<?php

namespace App\Http\Requests\Shop\Employee;

use App\Enums\EmploymentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'search' => trim((string) $this->input('search', '')),
            'status' => $this->input('status') === 'all' ? null : $this->input('status'),
            'branch' => $this->input('branch') === 'all' ? null : $this->input('branch'),
            'employment_type' => $this->input('employment_type') === 'all'
                ? null
                : $this->input('employment_type'),
        ]);
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(['Active', 'Inactive'])],
            'branch' => ['nullable', 'string', 'max:255'],
            'employment_type' => ['nullable', Rule::enum(EmploymentType::class)],
            'sort_by' => [
                'nullable',
                Rule::in([
                    'employee_id',
                    'full_name',
                    'position',
                    'employment_type',
                    'pay_rate',
                    'pay_basis',
                    'branch_name',
                    'phone',
                    'email',
                    'address',
                    'hire_date',
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
