<?php

namespace App\Http\Requests\Admin;

use App\Enums\AccountType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TogglePlatformPermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === AccountType::SuperAdmin->value;
    }

    public function rules(): array
    {
        $permissions = collect(config('platform_permissions.groups', []))
            ->flatMap(fn (array $group) => array_keys($group))
            ->all();

        return [
            'permission' => ['required', 'string', Rule::in($permissions)],
        ];
    }
}
