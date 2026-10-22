<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrderFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() === true;
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
            // Plain strings: old bookmarked links may still carry "all" for these.
            'status' => ['nullable', 'string', 'max:30'],
            'plan' => ['nullable', 'string', 'max:50'],
            'date' => ['nullable', 'date_format:Y-m-d'],
            'sort_by' => ['nullable', Rule::in([
                'reference', 'shop', 'owner', 'plan', 'total', 'status', 'expires', 'created_at',
            ])],
            'sort_direction' => ['nullable', Rule::in(['asc', 'desc'])],
            'per_page' => ['required', 'integer', 'between:5,100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * The validated filters with defaults filled in: newest first, 20 per page.
     *
     * @return array<string, mixed>
     */
    public function filters(): array
    {
        $validated = $this->validated();

        return [
            'search' => $validated['search'] ?? null,
            'status' => $validated['status'] ?? null,
            'plan' => $validated['plan'] ?? null,
            'date' => $validated['date'] ?? null,
            'sort_by' => $validated['sort_by'] ?? 'created_at',
            'sort_direction' => $validated['sort_direction'] ?? 'desc',
            'per_page' => (int) $validated['per_page'],
        ];
    }
}
