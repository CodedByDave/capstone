<?php

namespace App\Http\Requests\Admin;

use App\Models\IssueReport;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IssueReportFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
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
            'status' => ['nullable', Rule::in(IssueReport::STATUSES)],
            'priority' => ['nullable', Rule::in(IssueReport::PRIORITIES)],
            'category' => ['nullable', Rule::in(IssueReport::CATEGORIES)],
            'sort_by' => ['nullable', Rule::in([
                'subject', 'reporter', 'category', 'priority', 'status', 'created_at',
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
