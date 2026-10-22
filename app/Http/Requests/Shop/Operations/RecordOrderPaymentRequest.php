<?php

namespace App\Http\Requests\Shop\Operations;

use App\Models\ShopOrder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RecordOrderPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'reference' => is_string($this->reference) ? trim($this->reference) : $this->reference,
            'note' => is_string($this->note) ? trim($this->note) : $this->note,
        ]);
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'gt:0', 'max:9999999.99', 'decimal:0,2'],
            'method' => ['required', Rule::in(ShopOrder::PAYMENT_METHODS)],
            'reference' => ['nullable', 'string', 'max:100'],
            'note' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.gt' => 'The amount must be greater than zero.',
            'amount.decimal' => 'The amount can have at most 2 decimal places.',
            'method.required' => 'Choose how the customer paid.',
        ];
    }
}
