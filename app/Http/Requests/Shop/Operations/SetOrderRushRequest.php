<?php

namespace App\Http\Requests\Shop\Operations;

use Illuminate\Foundation\Http\FormRequest;

class SetOrderRushRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Who may change an order is decided by the route (owner, or staff with Operations manage).
        return true;
    }

    public function rules(): array
    {
        return [
            'rush' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'rush.required' => 'Choose whether the order is a rush order.',
        ];
    }
}
