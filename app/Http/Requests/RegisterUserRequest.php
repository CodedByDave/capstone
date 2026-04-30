<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $viaGoogle = $this->filled('google_id');

        return [
            'name'      => ['required', 'string', 'max:150'],
            'email'     => ['required', 'email', 'max:100', 'unique:users,email'],
            'password'  => $viaGoogle
                ? ['nullable', 'string']
                : ['required', 'string', 'confirmed', 'min:8'],
            'google_id' => ['nullable', 'string'],
        ];
    }
}
