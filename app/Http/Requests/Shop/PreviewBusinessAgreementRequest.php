<?php

namespace App\Http\Requests\Shop;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PreviewBusinessAgreementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isOwner() === true;
    }

    public function rules(): array
    {
        return [
            'business_name' => ['nullable', 'string', 'max:150'],
            'signer_name' => ['nullable', 'string', 'max:150'],
            'signer_role' => ['nullable', 'string', Rule::in(['Owner', 'Authorized Representative'])],
            'signature_image' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'signature_image.image' => 'The owner signature must be a valid image.',
            'signature_image.mimes' => 'The owner signature must be a PNG or JPG image.',
            'signature_image.max' => 'The owner signature image must not exceed 2MB.',
        ];
    }
}
