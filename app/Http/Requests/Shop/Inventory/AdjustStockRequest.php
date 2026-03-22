<?php

namespace App\Http\Requests\Shop\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class AdjustStockRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'type'             => ['required', 'in:restock,usage,adjustment,return,damage'],
            'quantity'         => ['required', 'integer', 'not_in:0'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'notes'            => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'quantity.not_in' => 'Quantity cannot be zero.',
        ];
    }

    /**
     * Ensure deduction types never send a positive quantity.
     * Restock/return = positive, usage/damage = negative.
     */
    protected function prepareForValidation(): void
    {
        $deductionTypes = ['usage', 'damage'];

        if (in_array($this->type, $deductionTypes) && $this->quantity > 0) {
            $this->merge(['quantity' => -abs($this->quantity)]);
        }

        if (in_array($this->type, ['restock', 'return']) && $this->quantity < 0) {
            $this->merge(['quantity' => abs($this->quantity)]);
        }
    }
}
