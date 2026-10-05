<?php

namespace App\Http\Requests\Shop;

use App\Rules\PhilippineMobileNumber;
use Illuminate\Foundation\Http\FormRequest;

class StartTrialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isOwner() === true;
    }

    public function rules(): array
    {
        return array_merge([
            'shop_name' => ['required', 'string', 'min:2', 'max:255'],
            'phone' => ['required', 'string', 'max:20', new PhilippineMobileNumber],
            'municipality' => ['required', 'string', 'max:255'],
            'barangay' => ['required', 'string', 'max:255'],
            'block_street' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:10'],
        ], AcceptBusinessAgreementRequest::agreementRules());
    }
}
