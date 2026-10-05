<?php

namespace App\Http\Requests\User;

use App\Rules\PhilippineMobileNumber;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'shop_id' => ['required', 'exists:shops,id'],
            'service_id' => ['required', 'exists:services_and_pricing,id'],
            'customer_name' => ['required', 'string', 'max:150'],
            'customer_phone' => ['required', 'string', 'max:20', new PhilippineMobileNumber],
            'estimated_weight_kg' => ['required', 'numeric', 'min:0.5', 'max:100'],
            'pickup_type' => ['required', 'in:walk_in,pickup'],
            'payment_method' => ['required', 'in:cash,gcash,maya,online'],
            'customer_address' => ['required_if:pickup_type,pickup', 'nullable', 'string', 'max:500'],
            'special_instructions' => ['nullable', 'string', 'max:500'],
        ];
    }
}
