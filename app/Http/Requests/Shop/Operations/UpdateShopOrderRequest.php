<?php

namespace App\Http\Requests\Shop\Operations;

use App\Models\ShopOrder;
use App\Rules\PhilippineMobileNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Once an order is placed its customer, fulfillment, service, pricing, payment and supplies are
 * locked. Only where the rider goes and the first weigh-in can still be saved from the edit page.
 */
class UpdateShopOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** A rider has to reach the customer: collecting the laundry from them, or bringing it back when it is done. */
    private function riderInvolved(): bool
    {
        $order = $this->route('order');

        return $order instanceof ShopOrder
            && ($order->pickup_type === 'pickup' || (bool) $order->deliver_to_customer);
    }

    public function rules(): array
    {
        return [
            // A rider needs an address to go to and someone else to call or hand the laundry to.
            'customer_address' => [Rule::requiredIf(fn () => $this->riderInvolved()), 'nullable', 'string', 'max:500'],
            'alternate_contact_name' => [Rule::requiredIf(fn () => $this->riderInvolved()), 'nullable', 'string', 'max:150', 'required_with:alternate_contact_phone'],
            'alternate_contact_phone' => [Rule::requiredIf(fn () => $this->riderInvolved()), 'nullable', 'string', 'max:20', 'required_with:alternate_contact_name', new PhilippineMobileNumber],
            // The first weigh-in of laundry that had no weight yet; a recorded weight is never changed here.
            'actual_weight_kg' => ['nullable', 'numeric', 'min:0.1', 'max:999.99'],
            // Photo of the laundry on the scale; the service requires it when an online order is weighed.
            'weight_proof' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'service_lines' => ['nullable', 'array', 'max:10'],
            'service_lines.*.weight_kg' => ['nullable', 'numeric', 'min:0.1', 'max:999.99'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_address.required' => 'Enter the address the rider should go to.',
            'alternate_contact_name.required' => 'Enter the name of the alternate contact.',
            'alternate_contact_phone.required' => 'Enter the alternate contact\'s mobile number.',
            'alternate_contact_name.required_with' => 'Enter the name of the alternate contact.',
            'alternate_contact_phone.required_with' => 'Enter the alternate contact\'s mobile number.',
        ];
    }
}
