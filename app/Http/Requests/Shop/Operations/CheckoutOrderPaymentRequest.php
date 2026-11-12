<?php

namespace App\Http\Requests\Shop\Operations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** The payment taken at the counter right after a new order is created. */
class CheckoutOrderPaymentRequest extends FormRequest
{
    public const MODES = ['unpaid', 'partial', 'full'];

    /** Only money handed over at the counter; online payments go through the customer's checkout. */
    public const METHODS = ['cash', 'gcash', 'maya'];

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        foreach (['amount', 'amount_tendered'] as $field) {
            if ($this->input($field) === '') {
                $this->merge([$field => null]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'payment_mode' => ['required', Rule::in(self::MODES)],
            'payment_method' => ['exclude_if:payment_mode,unpaid', 'required', Rule::in(self::METHODS)],
            'amount' => ['exclude_unless:payment_mode,partial', 'required', 'numeric', 'gt:0', 'max:9999999.99', 'decimal:0,2'],
            'amount_tendered' => ['exclude_unless:payment_method,cash', 'nullable', 'numeric', 'min:0', 'max:9999999.99'],
        ];
    }

    public function messages(): array
    {
        return [
            'payment_mode.required' => 'Choose how much the customer paid.',
            'payment_method.required' => 'Choose the payment type.',
            'payment_method.in' => 'Choose Cash, GCash or Maya.',
            'amount.required' => 'Enter the amount received.',
            'amount.gt' => 'The amount received must be more than zero.',
        ];
    }
}
