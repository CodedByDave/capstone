<?php

namespace App\Http\Requests\Shop;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRushFeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isOwner() === true;
    }

    protected function prepareForValidation(): void
    {
        // An empty field means the shop does not offer rush.
        if ($this->input('rush_fee') === '' || $this->input('rush_fee') === null) {
            $this->merge(['rush_fee' => 0]);
        }
    }

    public function rules(): array
    {
        return [
            'rush_fee' => ['required', 'numeric', 'min:0', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'rush_fee.numeric' => 'Enter the rush fee as an amount in pesos.',
            'rush_fee.min' => 'The rush fee cannot be negative.',
            'rush_fee.max' => 'The rush fee cannot be more than ₱5,000.',
        ];
    }
}
