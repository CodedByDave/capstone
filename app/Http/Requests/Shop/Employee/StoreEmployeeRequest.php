<?php

namespace App\Http\Requests\Shop\Employee;

use App\Enums\EmploymentType;
use App\Enums\PayBasis;
use App\Rules\PhilippineMobileNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email|unique:employees,email',
            'phone' => ['required', 'string', 'max:20', new PhilippineMobileNumber],
            'address' => 'required|string|max:500',
            'position' => 'required|string|max:255',
            'employment_type' => ['required', Rule::enum(EmploymentType::class)],
            'pay_rate' => ['required', 'numeric', 'min:0.01', 'max:9999999.99'],
            'pay_basis' => ['required', Rule::enum(PayBasis::class)],
            'hire_date' => 'required|date|before_or_equal:today',
            'status' => 'required|in:Active,Inactive',
            'create_account' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'employee_id.required' => 'Employee ID is required.',
            'employee_id.unique' => 'This Employee ID is already taken.',
            'first_name.required' => 'First name is required.',
            'last_name.required' => 'Last name is required.',
            'email.required' => 'Email is required.',
            'phone.required' => 'Phone number is required.',
            'address.required' => 'Address is required.',
            'address.min' => 'Please enter a complete address.',
            'position.required' => 'Position is required.',
            'employment_type.required' => 'Employment type is required.',
            'employment_type.enum' => 'Select a valid employment type.',
            'pay_rate.required' => 'Pay rate is required.',
            'pay_rate.numeric' => 'Pay rate must be a valid amount.',
            'pay_rate.min' => 'Pay rate must be greater than zero.',
            'pay_basis.required' => 'Pay basis is required.',
            'pay_basis.enum' => 'Select a valid pay basis.',
            'hire_date.required' => 'Hire date is required.',
            'hire_date.before_or_equal' => 'Hire date cannot be a future date.',
            'status.in' => 'Status must be Active or Inactive.',
        ];
    }
}
