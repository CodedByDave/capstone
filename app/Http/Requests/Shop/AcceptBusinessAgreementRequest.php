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

    /** The agreement is accepted by typed name, role and two confirmations; no signature is collected. */
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
        ];
    }

    public function messages(): array
    {
        return [
            'signer_name.required' => 'Type your full name to accept the agreement.',
            'signer_authority_confirmed.accepted' => 'You must confirm that you can bind this business.',
            'agreement_accepted.accepted' => 'You must accept the business agreement to continue.',
        ];
    }
}
