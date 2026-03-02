<?php

namespace App\Http\Requests\Shop;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Shop;

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
            'phone'       => 'nullable|string|max:20',
            'address'     => 'nullable|string|max:500',
            'position'    => 'required|string|max:255',
            'hire_date'   => 'required|date|before_or_equal:today',
            'salary'      => 'nullable|numeric|min:0|max:9999999.99',
            'status'      => 'required|in:Active,Inactive',
        ];
    }

    public function messages(): array
    {
        return [
            'employee_id.required' => 'Employee ID is required.',
            'employee_id.unique'   => 'This Employee ID is already taken.',
            'first_name.required'  => 'First name is required.',
            'last_name.required'   => 'Last name is required.',
            'position.required'    => 'Position is required.',
            'hire_date.required'   => 'Hire date is required.',
            'hire_date.before_or_equal' => 'Hire date cannot be a future date.',
            'status.in'            => 'Status must be Active or Inactive.',
            'salary.numeric'       => 'Salary must be a valid number.',
            'salary.min'           => 'Salary cannot be negative.',
        ];
    }
}
