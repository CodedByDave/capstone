<?php

namespace App\Http\Requests\Shop;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreKycVerificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isOwner() === true;
    }

    public function rules(): array
    {
        $file = ['file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'];

        return array_merge([
            // A document kept from a rejected verification may be reused instead of re-uploaded.
            'reuse_kyc' => ['nullable', 'array'],
            'reuse_kyc.*' => ['string', 'in:bir,dti,mayors,sanitary'],
            'kyc_bir' => [Rule::requiredIf(! $this->reuses('bir')), 'nullable', ...$file],
            'kyc_dti' => [Rule::requiredIf(! $this->reuses('dti')), 'nullable', ...$file],
            'kyc_mayors' => [Rule::requiredIf(! $this->reuses('mayors')), 'nullable', ...$file],
            'kyc_sanitary' => ['nullable', ...$file],
            'bir_expiry_date' => ['required', 'date', 'after:today'],
            'dti_expiry_date' => ['required', 'date', 'after:today'],
            'mayors_expiry_date' => ['required', 'date', 'after:today'],
            'sanitary_expiry_date' => ['nullable', 'date', 'after:today'],
            'kyc_confirmed' => ['required', 'array'],
            'kyc_confirmed.tin' => ['required', 'string', 'regex:/^\d{3}[\s-]?\d{3}[\s-]?\d{3}([\s-]?\d{3,5})?$/'],
            'kyc_confirmed.tax_type' => ['required', 'in:vat,non_vat'],
            'kyc_confirmed.registered_name' => ['required', 'string', 'min:2', 'max:255'],
            'kyc_confirmed.registered_address' => ['required', 'string', 'min:5', 'max:255'],
            // Acceptance only: the signer is the logged-in owner, so no name, capacity or signature is asked.
            'agreement_public_id' => ['required', 'string', 'max:26'],
            'agreement_accepted' => ['required', 'accepted'],
        ]);
    }

    public function messages(): array
    {
        return [
            'kyc_bir.required' => 'BIR Certificate of Registration is required.',
            'kyc_dti.required' => 'DTI Business Name Registration is required.',
            'kyc_mayors.required' => "Mayor's Business Permit is required.",
            'bir_expiry_date.after' => 'BIR expiry date must be in the future.',
            'dti_expiry_date.after' => 'DTI expiry date must be in the future.',
            'mayors_expiry_date.after' => "Mayor's permit expiry date must be in the future.",
            'sanitary_expiry_date.after' => 'Sanitary permit expiry date must be in the future.',
            'kyc_confirmed.tin.required' => 'Confirm the TIN read from your document.',
            'kyc_confirmed.tin.regex' => 'TIN must look like 123-456-789 or 123-456-789-000.',
            'kyc_confirmed.tax_type.required' => 'Choose whether the business is VAT or non-VAT registered.',
            'kyc_confirmed.tax_type.in' => 'Tax type must be VAT or Non-VAT.',
            'kyc_confirmed.registered_name.required' => 'Confirm the registered name.',
            'kyc_confirmed.registered_address.required' => 'Confirm the registered address.',
        ];
    }

    private function reuses(string $doc): bool
    {
        return in_array($doc, (array) $this->input('reuse_kyc', []), true);
    }
}
