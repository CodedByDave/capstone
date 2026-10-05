<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AuditLogEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'category' => $this->route('category'),
            'id' => $this->route('id'),
        ]);
    }

    public function rules(): array
    {
        return [
            'category' => ['required', Rule::in(['authentication', 'activity'])],
            'id' => ['required', 'integer', 'min:1'],
        ];
    }
}
