<?php

namespace App\Http\Requests\Shop\Employee;

use App\Enums\EmploymentType;
use App\Enums\PayBasis;
use App\Rules\PhilippineMobileNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'branch_name' => 'nullable|string|max:150',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => ['nullable', 'string', 'max:20', new PhilippineMobileNumber],
            'address' => 'nullable|string|max:500',
            'position' => 'required|string|max:255',
            'employment_type' => ['required', Rule::enum(EmploymentType::class)],
            'pay_rate' => ['required', 'numeric', 'min:0.01', 'max:9999999.99'],
            'pay_basis' => ['required', Rule::enum(PayBasis::class)],
            'hire_date' => 'required|date|before_or_equal:today',
            'status' => 'required|in:Active,Inactive',
            'create_account' => 'sometimes', 'nullable', 'integer', 'in:0,1',
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'First name is required.',
            'last_name.required' => 'Last name is required.',
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
