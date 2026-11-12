<?php

namespace App\Http\Requests\SupplierPortal;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** The supplier confirms an order (with a delivery date) or declines it (with a reason). */
class AnswerPurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        // The route is limited to active supplier logins.
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'note' => is_string($this->note) ? trim($this->note) : $this->note,
        ]);
    }

    public function rules(): array
    {
        return [
            'answer' => ['required', Rule::in(['confirm', 'decline'])],
            'expected_date' => ['exclude_unless:answer,confirm', 'required', 'date', 'after_or_equal:today'],
            'note' => ['nullable', 'required_if:answer,decline', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'expected_date.required' => 'Choose when you can deliver.',
            'expected_date.after_or_equal' => 'The delivery date cannot be in the past.',
            'note.required_if' => 'Tell the shop why you are declining.',
        ];
    }
}
