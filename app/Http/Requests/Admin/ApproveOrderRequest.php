<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ApproveOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() === true;
    }

    public function rules(): array
    {
        return [
            'platform_signer_name' => ['required', 'string', 'min:2', 'max:150'],
            'platform_signer_role' => ['required', 'string', 'min:2', 'max:150'],
            'platform_signature_method' => ['required', 'string', Rule::in(['drawn', 'uploaded'])],
            'platform_signature_image' => ['required', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
            'platform_signer_authority_confirmed' => ['required', 'accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'platform_signer_name.required' => 'Enter the authorized platform representative name.',
            'platform_signer_role.required' => 'Enter the representative position or signing capacity.',
            'platform_signature_image.required' => 'Draw or upload the platform representative signature.',
            'platform_signature_image.image' => 'The platform signature must be a valid image.',
            'platform_signature_image.mimes' => 'The platform signature must be a PNG or JPG image.',
            'platform_signature_image.max' => 'The platform signature image must not exceed 2MB.',
            'platform_signer_authority_confirmed.accepted' => 'Confirm your authority to sign for LaundryHub.',
        ];
    }
}
