<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShopRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Admins may only change the platform status. Identity, contact, address
     * and permit details belong to the shop owner; any other input is ignored.
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'in:active,pending,disabled'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Status is required.',
            'status.in' => 'Status must be active, pending, or disabled.',
        ];
    }
}
