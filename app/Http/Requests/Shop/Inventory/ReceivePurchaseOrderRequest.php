<?php

namespace App\Http\Requests\Shop\Inventory;

use Illuminate\Foundation\Http\FormRequest;

/** What actually arrived, per order line. A missing line means it arrived in full. */
class ReceivePurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'received' => ['nullable', 'array'],
            'received.*' => ['numeric', 'min:0', 'max:100000', 'decimal:0,2'],
        ];
    }

    /** @return array<int, mixed> */
    public function receivedQuantities(): array
    {
        return (array) ($this->validated('received') ?? []);
    }
}
