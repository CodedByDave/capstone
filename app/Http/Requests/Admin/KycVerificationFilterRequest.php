<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KycVerificationFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() === true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['per_page' => min(max($this->integer('per_page', 10), 5), 100)]);
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(['pending', 'approved', 'rejected'])],
            'sort_by' => ['nullable', Rule::in(['created_at', 'shop', 'status'])],
            'sort_direction' => ['nullable', Rule::in(['asc', 'desc'])],
            'per_page' => ['required', 'integer', 'between:5,100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /** @return array{search: ?string, status: ?string, sort_by: string, sort_direction: string, per_page: int} */
    public function filters(): array
    {
        $validated = $this->validated();

        return [
            'search' => $validated['search'] ?? null,
            'status' => $validated['status'] ?? null,
            'sort_by' => $validated['sort_by'] ?? 'created_at',
            'sort_direction' => $validated['sort_direction'] ?? 'desc',
            'per_page' => (int) $validated['per_page'],
        ];
    }
}
