<?php

namespace App\Http\Requests\User;

use App\Rules\PhilippineMobileNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\In;

class StoreUserOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'shop_id' => ['required', 'exists:shops,id', $this->ownShopRule()],
            'service_id' => ['required', 'exists:services_and_pricing,id'],
            'customer_name' => ['required', 'string', 'max:150'],
            'customer_phone' => ['required', 'string', 'max:20', new PhilippineMobileNumber],
            // Which of the two is needed depends on the service's pricing; the order service checks that.
            'estimated_weight_kg' => ['nullable', 'numeric', 'min:0.5', 'max:100'],
            'piece_count' => ['nullable', 'integer', 'min:1', 'max:500'],
            'pickup_type' => ['required', 'in:walk_in,pickup'],
            'customer_address' => ['required_if:pickup_type,pickup', 'nullable', 'string', 'max:500'],
            // Someone else the rider can reach or hand the laundry to if the customer is not home.
            'alternate_contact_name' => ['required_if:pickup_type,pickup', 'nullable', 'string', 'max:150'],
            'alternate_contact_phone' => ['required_if:pickup_type,pickup', 'nullable', 'string', 'max:20', new PhilippineMobileNumber],
            // The pinned pickup spot, so the rider can be followed on a map. Both or neither.
            'customer_lat' => ['nullable', 'required_with:customer_lng', 'numeric', 'between:-90,90'],
            'customer_lng' => ['nullable', 'required_with:customer_lat', 'numeric', 'between:-180,180'],
            'special_instructions' => ['nullable', 'string', 'max:500'],
            'customer_agreement_version' => ['required', 'string', 'max:30'],
            'customer_agreement_accepted' => ['required', 'accepted'],
        ];
    }

    /** Customers belong to the shop whose QR they joined through and can only order from it. */
    private function ownShopRule(): In
    {
        return Rule::in(array_filter([$this->user()?->referred_shop_id]));
    }

    public function messages(): array
    {
        return [
            'alternate_contact_name.required_if' => 'Enter the name of the alternate contact.',
            'alternate_contact_phone.required_if' => 'Enter the alternate contact\'s mobile number.',
            'customer_agreement_accepted.accepted' => 'You must review and accept the laundry service agreement before placing the order.',
        ];
    }
}
