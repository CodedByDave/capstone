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
        ];
    }

    public function messages(): array
    {
        return [
            'payment_method.required' => 'Select a payment method before continuing.',
            'payment_method.in' => 'Select GCash, Maya, Card, or GrabPay.',
        ];
    }
}
