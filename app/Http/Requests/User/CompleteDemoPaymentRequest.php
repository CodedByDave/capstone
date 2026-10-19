<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompleteDemoPaymentRequest extends FormRequest
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
}
