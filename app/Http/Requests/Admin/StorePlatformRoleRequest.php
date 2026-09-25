<?php

namespace App\Http\Requests\Admin;

use App\Enums\AccountType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StorePlatformRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === AccountType::SuperAdmin->value;
    }

    protected function prepareForValidation(): void
    {
        $name = trim((string) $this->input('name'));

        $this->merge([
            'name' => $name,
            'role_slug' => Str::slug($name, '_'),
            'description' => $this->filled('description')
                ? trim((string) $this->input('description'))
                : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50', Rule::unique('platform_roles', 'name')],
            'role_slug' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-z0-9]+(?:_[a-z0-9]+)*$/',
                Rule::unique('platform_roles', 'slug'),
            ],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'A role with this name already exists.',
            'role_slug.required' => 'Enter a valid role name.',
            'role_slug.regex' => 'Enter a valid role name.',
            'role_slug.unique' => 'A role with a similar name already exists.',
        ];
    }
}
