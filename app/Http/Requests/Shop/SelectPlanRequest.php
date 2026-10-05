<?php

namespace App\Http\Requests\Shop;

use Illuminate\Foundation\Http\FormRequest;

class SelectPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'plan_name' => ['required', 'string', 'in:Basic,Standard,Premium'],
            'billing_months' => ['required', 'integer', 'in:1,12,24,48'],
            'discount_pct' => ['required', 'integer'],
            'monthly_price' => ['required', 'integer'],
            'total_amount' => ['required', 'integer'],
        ];
    }
}
