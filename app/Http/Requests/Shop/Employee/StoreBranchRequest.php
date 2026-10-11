<?php

namespace App\Http\Requests\Shop\Employee;

use App\Rules\PhilippineMobileNumber;
use Illuminate\Foundation\Http\FormRequest;

class StoreBranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'branch_code' => ['required', 'string', 'max:20', 'unique:branches,branch_code'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20', new PhilippineMobileNumber],
            'email' => ['nullable', 'email', 'max:255'],
            'manager_name' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'opened_at' => ['nullable', 'date'],
            'status' => ['required', 'in:Active,Inactive'],
            'mayors_permit_number' => ['required', 'string', 'max:100'],
            'mayors_permit' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'mayors_expiry_date' => ['required', 'date', 'after:today'],
            'sanitary_permit_number' => ['nullable', 'string', 'max:100'],
            'sanitary_permit' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'sanitary_expiry_date' => ['nullable', 'date', 'after:today', 'required_with:sanitary_permit'],
        ];
    }

    public function messages(): array
    {
        return [
            'branch_code.required' => 'Branch code is required.',
            'branch_code.unique' => 'This branch code is already taken.',
            'name.required' => 'Branch name is required.',
            'status.required' => 'Status is required.',
            'status.in' => 'Status must be Active or Inactive.',
            'email.email' => 'Please enter a valid email address.',
            'mayors_permit.required' => "Upload this branch's Mayor's permit.",
            'mayors_expiry_date.after' => "The Mayor's permit expiry date must be in the future.",
        ];
    }
}
