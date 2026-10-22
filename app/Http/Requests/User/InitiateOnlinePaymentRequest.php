<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InitiateOnlinePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isUser() === true;
    }

    public function rules(): array
    {
        return [
            'payment_method' => ['required', Rule::in(['gcash', 'maya', 'card', 'grabpay'])],
            // Left out to pay the whole remaining balance.
            'amount' => ['nullable', 'numeric', 'min:100', 'max:9999999.99', 'decimal:0,2'],
        ];
    }

    public function messages(): array
    {
        return [
            'payment_method.required' => 'Select a payment method before continuing.',
            'amount.min' => 'The smallest amount you can pay online is ₱100.',
            'amount.decimal' => 'The amount can have at most 2 decimal places.',
            'payment_method.in' => 'Select GCash, Maya, Card, or GrabPay.',
        ];
    }
}
