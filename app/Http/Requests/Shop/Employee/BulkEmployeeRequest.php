<?php

namespace App\Http\Requests\Shop\Employee;

use Illuminate\Foundation\Http\FormRequest;

class BulkEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', 'exists:employees,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'ids.required'  => 'No employees were selected.',
            'ids.array'     => 'Invalid selection format.',
            'ids.min'       => 'Please select at least one employee.',
            'ids.*.integer' => 'Invalid employee ID.',
            'ids.*.exists'  => 'One or more selected employees do not exist.',
        ];
    }
}
