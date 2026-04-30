<?php

namespace App\Http\Requests\Shop;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'plan_name'      => ['required', 'string', 'in:Basic,Standard,Premium'],
            'billing_months' => ['required', 'integer', 'in:1,12,24,48'],
            'shop_name'      => ['required', 'string', 'min:2', 'max:255'],
            'email'          => ['required', 'email', 'max:255'],
            'phone'          => ['required', 'string', 'min:10', 'max:20'],
            'block_street'   => ['nullable', 'string', 'max:255'],
            'municipality'   => ['required', 'string', 'max:255'],
            'barangay'       => ['required', 'string', 'max:255'],
            'postal_code'    => ['required', 'string', 'max:10'],
            'payment_method' => ['nullable', 'string', 'in:gcash,maya,card,grab_pay,dob,billease'],
            'kyc_bir'        => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'kyc_dti'        => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'kyc_mayors'     => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'kyc_sanitary'   => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'shop_name.required'    => 'Business name is required.',
            'shop_name.min'         => 'Business name must be at least 2 characters.',
            'phone.required'        => 'Phone number is required.',
            'phone.min'             => 'Phone number must be at least 10 digits.',
            'municipality.required' => 'Municipality is required.',
            'barangay.required'     => 'Barangay is required.',
            'postal_code.required'  => 'Postal code is required.',

            'kyc_bir.required'    => 'BIR Certificate of Registration is required.',
            'kyc_bir.mimes'       => 'BIR document must be a PDF, JPG, or PNG.',
            'kyc_bir.max'         => 'BIR document must not exceed 5MB.',
            'kyc_dti.required'    => 'DTI Business Name Registration is required.',
            'kyc_dti.mimes'       => 'DTI document must be a PDF, JPG, or PNG.',
            'kyc_dti.max'         => 'DTI document must not exceed 5MB.',
            'kyc_mayors.required' => "Mayor's Business Permit is required.",
            'kyc_mayors.mimes'    => "Mayor's permit must be a PDF, JPG, or PNG.",
            'kyc_mayors.max'      => "Mayor's permit must not exceed 5MB.",
            'kyc_sanitary.mimes'  => 'Sanitary permit must be a PDF, JPG, or PNG.',
            'kyc_sanitary.max'    => 'Sanitary permit must not exceed 5MB.',
        ];
    }

    public function failedAuthorization()
    {
        return redirect()->route('login')->with('toast', [
            'type'    => 'error',
            'message' => 'You must be logged in to complete checkout.',
        ]);
    }
}
