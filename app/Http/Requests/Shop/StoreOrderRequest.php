<?php

namespace App\Http\Requests\Shop;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'branch_name' => 'nullable|string|max:255',
            'shop_name' => 'required|string|max:255',
            'owner_name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|digits_between:10,11',
            'neighborhood' => 'required|string|max:255',
            'municipality' => 'required|string',
            'barangay' => 'required|string',
            'postal_code' => 'nullable|string',
            'modules' => 'required|array|min:1',
            'modules.*.name' => 'required|string',
            'modules.*.price' => 'required|numeric|min:0',
            'payment_method' => 'nullable|string',
            'amount' => 'nullable|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'modules.required' => 'Please select at least one module.',
            'modules.min' => 'Please select at least one module.',
        ];
    }
}
