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
            'shop_name'       => ['required', 'string'],
            'owner_name'      => ['required', 'string'],
            'email'           => ['required', 'email'],
            'phone'           => ['required', 'string'],
            'block_street'    => ['required', 'string'],
            'municipality'    => ['required', 'string'],
            'barangay'        => ['required', 'string'],
            'postal_code'     => ['required', 'string'],
            'branch_name'     => ['nullable', 'string'],
            'amount'          => ['required', 'numeric'],
            'modules'         => ['required', 'array', 'min:1'],
            'modules.*.name'  => ['required', 'string'],
            'modules.*.price' => ['required', 'numeric'],
        ];
    }

    public function messages(): array
    {
        return [
            'modules.required' => 'Please select at least one module.',
            'modules.min'      => 'Please select at least one module.',
        ];
    }
}
