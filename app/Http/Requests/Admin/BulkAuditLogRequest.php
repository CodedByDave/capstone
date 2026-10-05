<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkAuditLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'entries' => ['required', 'array', 'min:1'],
            'entries.*.category' => ['required', 'string', Rule::in(['authentication', 'activity'])],
            'entries.*.id' => ['required', 'integer', 'min:1'],
        ];
    }

    public function entries(): array
    {
        return $this->validated('entries');
    }
}
