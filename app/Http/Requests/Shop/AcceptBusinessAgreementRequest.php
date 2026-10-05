<?php

namespace App\Http\Requests\Shop;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AcceptBusinessAgreementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isOwner() === true;
    }

    public function rules(): array
    {
        return self::agreementRules();
    }

    public static function agreementRules(): array
    {
        return [
            'agreement_public_id' => ['required', 'string', 'max:26'],
            'signer_name' => ['required', 'string', 'min:2', 'max:150'],
            'signer_role' => [
                'required',
                'string',
                Rule::in(['Owner', 'Authorized Representative']),
            ],
            'signer_authority_confirmed' => ['required', 'accepted'],
            'agreement_accepted' => ['required', 'accepted'],
            'signature_method' => ['nullable', 'string', Rule::in(['drawn', 'uploaded'])],
            'signature_image' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'signer_name.required' => 'Type your full name to sign the agreement.',
            'signer_authority_confirmed.accepted' => 'You must confirm that you can bind this business.',
            'agreement_accepted.accepted' => 'You must accept the business agreement to continue.',
            'signature_image.image' => 'The owner signature must be a valid image.',
            'signature_image.mimes' => 'The owner signature must be a PNG or JPG image.',
            'signature_image.max' => 'The owner signature image must not exceed 2MB.',
        ];
    }
}
