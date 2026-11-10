<?php

namespace App\Http\Requests\Settings;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
                // A supplier signs in with the email their shop keeps on file, so only the shop changes it.
                ...($this->user()->isSupplier() ? [Rule::in([$this->user()->email])] : []),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'email.in' => 'Your email is managed by the shop you supply. Ask them to change it.',
        ];
    }
}
