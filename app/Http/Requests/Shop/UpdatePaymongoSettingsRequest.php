<?php

namespace App\Http\Requests\Shop;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymongoSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isOwner() === true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'paymongo_secret_key' => $this->filled('paymongo_secret_key')
                ? trim((string) $this->input('paymongo_secret_key'))
                : null,
            'paymongo_public_key' => $this->filled('paymongo_public_key')
                ? trim((string) $this->input('paymongo_public_key'))
                : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'paymongo_secret_key' => ['nullable', 'required_with:paymongo_public_key', 'string', 'max:300', 'regex:/^sk_(test|live)_[A-Za-z0-9]+$/'],
            'paymongo_public_key' => ['nullable', 'required_with:paymongo_secret_key', 'string', 'max:300', 'regex:/^pk_(test|live)_[A-Za-z0-9]+$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'paymongo_secret_key.regex' => 'Secret key must start with sk_test_ or sk_live_.',
            'paymongo_public_key.regex' => 'Public key must start with pk_test_ or pk_live_.',
            'paymongo_secret_key.required_with' => 'Enter both the secret key and public key.',
            'paymongo_public_key.required_with' => 'Enter both the secret key and public key.',
        ];
    }
}
