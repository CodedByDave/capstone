<?php

namespace App\Http\Requests\SupplierPortal;

use Illuminate\Foundation\Http\FormRequest;

/** The supplier marks an order delivered and attaches a photo of the delivery. */
class DeliverPurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        // The route is limited to active supplier logins.
        return true;
    }

    public function rules(): array
    {
        return [
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'photo.required' => 'Add a photo of the delivery.',
            'photo.image' => 'The delivery photo must be an image.',
            'photo.mimes' => 'The delivery photo must be a JPG, PNG or WEBP image.',
            'photo.max' => 'The delivery photo must be 5 MB or smaller.',
        ];
    }
}
