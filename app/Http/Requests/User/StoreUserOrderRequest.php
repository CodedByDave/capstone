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
            // Which of the two is needed depends on the service's pricing; the order service checks that.
            'estimated_weight_kg' => ['nullable', 'numeric', 'min:0.5', 'max:100'],
            'piece_count' => ['nullable', 'integer', 'min:1', 'max:500'],
            'pickup_type' => ['required', 'in:walk_in,pickup'],
            'payment_method' => ['required', 'in:cash,gcash,maya,online'],
            'customer_address' => ['required_if:pickup_type,pickup', 'nullable', 'string', 'max:500'],
            // The pinned pickup spot, so the rider can be followed on a map. Both or neither.
            'customer_lat' => ['nullable', 'required_with:customer_lng', 'numeric', 'between:-90,90'],
            'customer_lng' => ['nullable', 'required_with:customer_lat', 'numeric', 'between:-180,180'],
            'special_instructions' => ['nullable', 'string', 'max:500'],
            'customer_agreement_version' => ['required', 'string', 'max:30'],
            'customer_agreement_accepted' => ['required', 'accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_agreement_accepted.accepted' => 'You must review and accept the laundry service agreement before placing the order.',
        ];
    }
}
