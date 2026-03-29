<?php

namespace App\Http\Requests\Shop;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutProcessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && session()->has('checkout');
    }

    public function rules(): array
    {
        return [
            'shop_name'    => 'required|string|min:2|max:255',
            'phone'        => 'required|string|min:10|max:20',
            'block_street' => 'required|string|min:5|max:255',
            'municipality' => 'required|string|max:255',
            'barangay'     => 'required|string|max:255',
            'postal_code'  => 'required|string|max:10',
            'branch_name'  => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'shop_name.required'    => 'Shop name is required.',
            'shop_name.min'         => 'Shop name must be at least 2 characters.',
            'phone.required'        => 'Phone number is required.',
            'phone.min'             => 'Phone number must be at least 10 digits.',
            'block_street.required' => 'Block/Street address is required.',
            'block_street.min'      => 'Please enter a complete address.',
            'municipality.required' => 'Municipality is required.',
            'barangay.required'     => 'Barangay is required.',
            'postal_code.required'  => 'Postal code is required.',
        ];
    }

    public function failedAuthorization()
    {
        return redirect()->route('landing')->with('toast', [
            'type'    => 'error',
            'message' => 'No active checkout session. Please select a plan first.',
        ]);
    }
}
