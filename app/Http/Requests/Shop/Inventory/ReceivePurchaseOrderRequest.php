<?php

namespace App\Http\Requests\Shop\Inventory;

use Illuminate\Foundation\Http\FormRequest;

/** What actually arrived, per order line (a missing line means it arrived in full), with the shop's photo of the delivery. */
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
            'proof' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'proof.required' => 'Add a photo of the delivery you received.',
            'proof.image' => 'The delivery photo must be an image.',
            'proof.mimes' => 'The delivery photo must be a JPG, PNG or WEBP image.',
            'proof.max' => 'The delivery photo must be 5 MB or smaller.',
        ];
    }

    /** @return array<int, mixed> */
    public function receivedQuantities(): array
    {
        return (array) ($this->validated('received') ?? []);
    }
}
