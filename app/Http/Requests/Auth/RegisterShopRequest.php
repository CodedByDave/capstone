<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterShopRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|confirmed|min:8',

            'shop_name' => 'required|string|max:150',
            'phone' => 'required|string|max:20',
            'branch_name' => 'nullable|string|max:150',
            'block_street' => 'required|string|max:255',
            'municipality' => 'required|string|max:255',
            'barangay' => 'required|string|max:255',
            'postal_code' => 'required|string|max:10',
            'google_id' => 'nullable|string'
        ];
    }
}
