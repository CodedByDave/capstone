<?php

namespace App\Http\Requests\Shop\Operations;

use App\Models\ShopOrder;
use App\Rules\PhilippineMobileNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateShopOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        $validator->after(function (\Illuminate\Validation\Validator $validator) {
            $order = $this->route('order');

            if ($this->boolean('deliver_to_customer')
                && ! $order?->shop()->where('offers_delivery', true)->whereNotNull('delivery_pricing_set_at')->exists()) {
                $validator->errors()->add('deliver_to_customer', 'Delivery is not available until the shop sets its delivery pricing.');
            }
        });
    }

    // In UpdateShopOrderRequest
    /** A rider has to reach the customer: collecting the laundry from them, or bringing it back when it is done. */
    private function riderInvolved(): bool
    {
        return $this->input('pickup_type') === 'pickup' || $this->boolean('deliver_to_customer');
    }

    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:255'],
            // A rider needs a number to call and an address to go to on a delivery pickup order.
            'customer_phone' => [Rule::requiredIf(fn () => $this->riderInvolved()), 'nullable', 'string', 'max:20', new PhilippineMobileNumber],
            // Someone else the rider can reach or hand the laundry to, required on a delivery pickup order.
            'alternate_contact_name' => [Rule::requiredIf(fn () => $this->riderInvolved()), 'nullable', 'string', 'max:150', 'required_with:alternate_contact_phone'],
            'alternate_contact_phone' => [Rule::requiredIf(fn () => $this->riderInvolved()), 'nullable', 'string', 'max:20', 'required_with:alternate_contact_name', new PhilippineMobileNumber],
            'customer_address' => [Rule::requiredIf(fn () => $this->riderInvolved()), 'nullable', 'string', 'max:500', 'required_if_accepted:deliver_to_customer'],
            'deliver_to_customer' => ['nullable', 'boolean'],
            'service_id' => ['required', 'integer', Rule::exists('services_and_pricing', 'id')],
            'pickup_type' => ['required', Rule::in(ShopOrder::PICKUP_TYPES)],
            'pricing_model' => ['required', Rule::in(['per_kg', 'per_bundle', 'per_piece'])],
            'piece_count' => [
                Rule::requiredIf(fn () => $this->input('pricing_model') === 'per_piece'),
                'nullable',
                'integer',
                'min:1',
                'max:500',
            ],
            // Orders created by staff have no estimate, so it is optional here too.
            'estimated_weight_kg' => [
                'nullable',
                'numeric',
                'min:0.1',
            ],
            'actual_weight_kg' => ['nullable', 'numeric', 'min:0.1', 'max:999.99'],
            'price_per_kg' => ['nullable', 'numeric', 'min:0'],
            'bundle_weight_kg' => [
                Rule::requiredIf(fn () => $this->input('pricing_model') === 'per_bundle'),
                'nullable',
                'numeric',
                'min:0.1',
            ],
            'bundle_price' => [
                Rule::requiredIf(fn () => $this->input('pricing_model') === 'per_bundle'),
                'nullable',
                'numeric',
                'min:0',
            ],
            'bundle_quantity' => [
                Rule::requiredIf(fn () => $this->input('pricing_model') === 'per_bundle'),
                'nullable',
                'integer',
                'min:1',
            ],
            'promotion_id' => ['nullable', 'integer', Rule::exists('promotions', 'id')->where('shop_id', app(\App\Services\ShopContext::class)->shopIdFor($this->user()))],
            'additional_charges' => ['nullable', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'total_amount' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['required', Rule::in(ShopOrder::PAYMENT_METHODS)],
            // What the staff chose in the form: mark the order fully paid, or take its payments back.
            'payment_state' => ['nullable', Rule::in(['paid', 'unpaid'])],
            'status' => ['required', Rule::in(ShopOrder::STATUSES)],           // ← add
            'estimated_completion_at' => ['nullable', 'date'],
            'supplies' => ['nullable', 'array'],
            'supplies.*.inventory_id' => ['required', 'integer', 'exists:inventory,id'],
            'supplies.*.quantity_used' => ['required', 'numeric', 'min:0.01'],
            'supplies.*.unit' => ['required', 'string', 'max:50'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'additional_charges' => $this->additional_charges ?? 0,
            'discount_amount' => $this->discount_amount ?? 0,
            'price_per_kg' => $this->price_per_kg ?? 0,
        ]);
    }

    public function messages(): array
    {
        return [
            'customer_phone.required' => 'Enter the customer\'s mobile number so the rider can reach them.',
            'customer_address.required' => 'Enter the address the rider should go to.',
            'alternate_contact_name.required' => 'Enter the name of the alternate contact.',
            'alternate_contact_phone.required' => 'Enter the alternate contact\'s mobile number.',
            'alternate_contact_name.required_with' => 'Enter the name of the alternate contact.',
            'alternate_contact_phone.required_with' => 'Enter the alternate contact\'s mobile number.',
            'service_id.exists' => 'The selected service does not exist.',
            'supplies.*.inventory_id.exists' => 'One or more selected supplies do not exist in inventory.',
            'supplies.*.quantity_used.min' => 'Quantity used must be at least 0.01.',
        ];
    }
}
