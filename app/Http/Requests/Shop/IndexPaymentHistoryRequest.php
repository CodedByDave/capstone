<?php

namespace App\Http\Requests\Shop;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexPaymentHistoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'owner';
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'search' => trim((string) $this->input('search', '')),
        ]);
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'payment_method' => ['nullable', Rule::in(['gcash', 'maya', 'card', 'grab_pay', 'dob', 'billease'])],
            'sort_by' => ['nullable', Rule::in(['paid_at', 'amount', 'payment_method', 'transaction_reference', 'plan_name'])],
            'sort_direction' => ['nullable', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', Rule::in([5, 10, 15, 20, 50])],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
