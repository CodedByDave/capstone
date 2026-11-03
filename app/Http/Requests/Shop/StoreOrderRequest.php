<?php

namespace App\Http\Requests\Shop;

use App\Repositories\KycVerificationRepository;
use App\Rules\PhilippineMobileNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isOwner() === true;
    }

    public function rules(): array
    {
        $verified = $this->user() && app(KycVerificationRepository::class)->isVerifiedOwner($this->user()->id);

        return array_merge($verified ? [] : $this->kycRules(), [
            'plan_name' => ['required', 'string', 'in:Basic,Standard,Premium'],
            'billing_months' => ['required', 'integer', 'in:1,12,24,48'],
            'shop_name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:20', new PhilippineMobileNumber],
            'block_street' => ['nullable', 'string', 'max:255'],
            'municipality' => ['required', 'string', 'max:255'],
            'barangay' => ['required', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:10'],
            'payment_method' => ['nullable', 'string', 'in:gcash,maya,card,grab_pay,dob,billease'],
        ], AcceptBusinessAgreementRequest::agreementRules());
    }

    /** KYC rules apply only to owners an admin has not verified yet. */
    private function kycRules(): array
    {
        return [
            // A document kept from a rejected application may be reused instead of re-uploaded.
            'reuse_kyc' => ['nullable', 'array'],
            'reuse_kyc.*' => ['string', 'in:bir,dti,mayors,sanitary'],
            'kyc_bir' => [Rule::requiredIf(! $this->reuses('bir')), 'nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'kyc_dti' => [Rule::requiredIf(! $this->reuses('dti')), 'nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'kyc_mayors' => [Rule::requiredIf(! $this->reuses('mayors')), 'nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'kyc_confirmed' => ['required', 'array'],
            'kyc_confirmed.tin' => ['required', 'string', 'regex:/^\d{3}[\s-]?\d{3}[\s-]?\d{3}([\s-]?\d{3,5})?$/'],
            'kyc_confirmed.registered_name' => ['required', 'string', 'min:2', 'max:255'],
            'kyc_confirmed.registered_address' => ['required', 'string', 'min:5', 'max:255'],
            'kyc_sanitary' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'bir_expiry_date' => ['required', 'date', 'after:today'],
            'mayors_expiry_date' => ['required', 'date', 'after:today'],
            'dti_expiry_date' => ['required', 'date', 'after:today'],
            'sanitary_expiry_date' => ['nullable', 'date', 'after:today'],
        ];
    }

    private function reuses(string $doc): bool
    {
        return in_array($doc, (array) $this->input('reuse_kyc', []), true);
    }

    public function messages(): array
    {
        return [
            'kyc_confirmed.tin.required' => 'Confirm the TIN read from your document.',
            'kyc_confirmed.tin.regex' => 'TIN must look like 123-456-789 or 123-456-789-000.',
            'kyc_confirmed.registered_name.required' => 'Confirm the registered name.',
            'kyc_confirmed.registered_address.required' => 'Confirm the registered address.',
            'shop_name.required' => 'Business name is required.',
            'shop_name.min' => 'Business name must be at least 2 characters.',
            'phone.required' => 'Phone number is required.',
            'phone.min' => 'Phone number must be at least 10 digits.',
            'municipality.required' => 'Municipality is required.',
            'barangay.required' => 'Barangay is required.',
            'postal_code.required' => 'Postal code is required.',

            'kyc_bir.required' => 'BIR Certificate of Registration is required.',
            'kyc_bir.mimes' => 'BIR document must be a PDF, JPG, or PNG.',
            'kyc_bir.max' => 'BIR document must not exceed 5MB.',
            'kyc_dti.required' => 'DTI Business Name Registration is required.',
            'kyc_dti.mimes' => 'DTI document must be a PDF, JPG, or PNG.',
            'kyc_dti.max' => 'DTI document must not exceed 5MB.',
            'kyc_mayors.required' => "Mayor's Business Permit is required.",
            'kyc_mayors.mimes' => "Mayor's permit must be a PDF, JPG, or PNG.",
            'kyc_mayors.max' => "Mayor's permit must not exceed 5MB.",
            'kyc_sanitary.mimes' => 'Sanitary permit must be a PDF, JPG, or PNG.',
            'kyc_sanitary.max' => 'Sanitary permit must not exceed 5MB.',
            'mayors_expiry_date.required' => "Mayor's permit expiry date is required.",
            'mayors_expiry_date.after' => "Mayor's permit expiry date must be in the future.",
            'dti_expiry_date.required' => 'DTI registration expiry date is required.',
            'dti_expiry_date.after' => 'DTI expiry date must be in the future.',
            'sanitary_expiry_date.after' => 'Sanitary permit expiry date must be in the future.',
        ];
    }

    public function failedAuthorization()
    {
        return redirect()->route('login')->with('toast', [
            'type' => 'error',
            'message' => 'You must be logged in to complete checkout.',
        ]);
    }
}
