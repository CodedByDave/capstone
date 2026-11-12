<?php

namespace App\Http\Requests\Shop\Operations;

use Illuminate\Foundation\Http\FormRequest;

/** The customer's pinned location, for the delivery fee shown while an order is taken at the counter. */
class QuoteCounterDeliveryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
        ];
    }
}
