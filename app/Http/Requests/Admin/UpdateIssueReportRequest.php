<?php

namespace App\Http\Requests\Admin;

use App\Models\IssueReport;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateIssueReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(IssueReport::STATUSES)],
            'priority' => ['required', Rule::in(IssueReport::PRIORITIES)],
            'admin_notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
