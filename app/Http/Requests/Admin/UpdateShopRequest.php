<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShopRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'shop_name'    => ['required', 'string', 'max:255'],
            'branch_name'  => ['nullable', 'string', 'max:255'],
            'phone'        => ['required', 'string', 'max:255'],
            'block_street' => ['nullable', 'string', 'max:255'],
            'municipality' => ['required', 'string', 'max:255'],
            'barangay'     => ['required', 'string', 'max:255'],
            'postal_code'  => ['required', 'string', 'max:255'],
            'status'       => ['required', 'in:active,inactive,pending'],
        ];
    }

    public function messages(): array
    {
        return [
            'shop_name.required'    => 'Shop name is required.',
            'phone.required'        => 'Phone number is required.',
            'municipality.required' => 'Municipality is required.',
            'barangay.required'     => 'Barangay is required.',
            'postal_code.required'  => 'Postal code is required.',
            'status.required'       => 'Status is required.',
            'status.in'             => 'Status must be active, inactive, or pending.',
        ];
    }
}
