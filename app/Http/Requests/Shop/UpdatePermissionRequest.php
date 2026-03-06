<?php

namespace App\Http\Requests\Shop;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'role'   => ['required', 'string'],
            'module' => ['required', 'string'],
            'action' => ['required', 'string'],
        ];
    }
}
