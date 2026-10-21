<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class EstimateOrderBreakdownRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'shop_id' => ['required', 'integer'],
            'service_id' => ['required', 'integer'],
            'estimated_weight_kg' => ['required', 'numeric', 'min:0.5', 'max:100'],
        ];
    }
}
