<?php

namespace App\Http\Requests\Shop\Operations;

use Illuminate\Foundation\Http\FormRequest;

class SendOrderForDeliveryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isOwner() === true || $this->user()?->isStaff() === true;
    }

    public function rules(): array
    {
        return [
            // Only needed when the order has no address on file yet.
            'delivery_address' => ['nullable', 'string', 'max:500'],
        ];
    }
}
