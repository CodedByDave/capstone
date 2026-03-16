<?php

namespace App\Http\Requests\Shop;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $branchId = $this->route('branch')?->id;

        return [
            'branch_code'  => ['required', 'string', 'max:20', "unique:branches,branch_code,{$branchId}"],
            'name'         => ['required', 'string', 'max:255'],
            'phone'        => ['nullable', 'string', 'max:20'],
            'email'        => ['nullable', 'email', 'max:255'],
            'manager_name' => ['nullable', 'string', 'max:255'],
            'address'      => ['nullable', 'string', 'max:500'],
            'opened_at'    => ['nullable', 'date'],
            'status'       => ['required', 'in:Active,Inactive'],
        ];
    }

    public function messages(): array
    {
        return [
            'branch_code.required' => 'Branch code is required.',
            'branch_code.unique'   => 'This branch code is already taken.',
            'name.required'        => 'Branch name is required.',
            'status.required'      => 'Status is required.',
            'status.in'            => 'Status must be Active or Inactive.',
            'email.email'          => 'Please enter a valid email address.',
        ];
    }
}
