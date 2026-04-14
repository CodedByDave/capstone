<?php

namespace App\Http\Requests\Shop;

use Illuminate\Foundation\Http\FormRequest;

class StoreUpgradeRequest extends FormRequest
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
            'payment_method' => ['required', 'string', 'in:gcash,maya,card,grab_pay,dob,billease'],
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
