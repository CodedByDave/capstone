<?php

namespace App\Http\Requests\Shop\Employee;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => 'required|string|max:50|unique:employees,employee_id',
            'branch_name' => 'nullable|string|max:150',
            'first_name'  => 'required|string|max:255',
            'last_name'   => 'required|string|max:255',
            'email'       => 'required|email|max:255|unique:users,email|unique:employees,email',
            'phone'       => 'required|regex:/^09\d{9}$/',
            'address'     => 'required|string|max:500',
            'position'    => 'required|string|max:255',
            'hire_date'   => 'required|date|before_or_equal:today',
            'salary'      => 'required|numeric|min:13000|max:50000',
            'status'      => 'required|in:Active,Inactive',
            'create_account' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'employee_id.required' => 'Employee ID is required.',
            'employee_id.unique'   => 'This Employee ID is already taken.',
            'first_name.required'  => 'First name is required.',
            'last_name.required'   => 'Last name is required.',
            'email.required'       => 'Email is required.',
            'phone.required'       => 'Phone number is required.',
            'address.required'     => 'Address is required.',
            'address.min'          => 'Please enter a complete address.',
            'position.required'    => 'Position is required.',
            'hire_date.required'   => 'Hire date is required.',
            'hire_date.before_or_equal' => 'Hire date cannot be a future date.',
            'status.in'            => 'Status must be Active or Inactive.',
            'salary.numeric'       => 'Salary cannot exceed ₱50,000 per month.',
            'salary.min'           => 'Salary must be at least ₱13,000 per month.',
        ];
    }
}

