<?php

namespace App\Http\Requests\Admin;

use App\Enums\AccountType;
use Illuminate\Foundation\Http\FormRequest;

class DeletePlatformRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === AccountType::SuperAdmin->value;
    }

    public function rules(): array
    {
        return [];
    }
}
