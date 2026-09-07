<?php

namespace App\Http\Requests\Shop;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;

class StoreUpgradeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        // Trial users are submitting their first paid plan — require KYC like a new shop.
        // Paid→paid upgrades are already verified — skip KYC.
        $isTrial = (bool) Order::where('user_id', auth()->id())
            ->whereNotIn('status', ['rejected'])
            ->latest()
            ->value('is_trial');

        $kycRule = $isTrial
            ? ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120']
            : ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'];

        return [
            'plan_name'          => ['required', 'string', 'in:Basic,Standard,Premium'],
            'billing_months'     => ['required', 'integer', 'in:1,12,24,48'],
            'payment_method'     => $isTrial
                ? ['nullable', 'string', 'in:gcash,maya,card,grab_pay,dob,billease']
                : ['required', 'string', 'in:gcash,maya,card,grab_pay,dob,billease'],
            'kyc_bir'            => $kycRule,
            'kyc_dti'            => $kycRule,
            'kyc_mayors'          => $kycRule,
            'kyc_sanitary'        => $isTrial
                ? ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120']
                : ['nullable'],
            'bir_expiry_date'     => $isTrial ? ['required', 'date', 'after:today'] : ['nullable', 'date'],
            'dti_expiry_date'     => $isTrial ? ['required', 'date', 'after:today'] : ['nullable', 'date'],
            'mayors_expiry_date'  => $isTrial ? ['required', 'date', 'after:today'] : ['nullable', 'date'],
            'sanitary_expiry_date'=> $isTrial ? ['nullable', 'date', 'after:today'] : ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'kyc_bir.required'             => 'BIR Certificate of Registration is required.',
            'kyc_dti.required'             => 'DTI Business Name Registration is required.',
            'kyc_mayors.required'          => "Mayor's Business Permit is required.",
            'mayors_expiry_date.required'  => "Mayor's permit expiry date is required.",
            'mayors_expiry_date.after'     => "Mayor's permit expiry date must be in the future.",
            'dti_expiry_date.required'     => 'DTI registration expiry date is required.',
            'dti_expiry_date.after'        => 'DTI expiry date must be in the future.',
            'sanitary_expiry_date.after'   => 'Sanitary permit expiry date must be in the future.',
            'payment_method.required'      => 'Please select a payment method.',
        ];
    }

    public function failedAuthorization()
    {
        return redirect()->route('login')->with('toast', [
            'type'    => 'error',
            'message' => 'You must be logged in to upgrade your plan.',
        ]);
    }
}
