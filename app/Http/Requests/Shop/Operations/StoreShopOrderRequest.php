<?php

namespace App\Http\Requests\Shop\Operations;

use App\Models\ShopOrder;
use App\Rules\PhilippineMobileNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreShopOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'additional_charges' => $this->additional_charges ?? 0,
            'discount_amount' => $this->discount_amount ?? 0,
            'amount_paid' => $this->amount_paid ?? 0,
            'price_per_kg' => $this->price_per_kg ?? 0,
        ]);
    }

    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        $validator->after(function (\Illuminate\Validation\Validator $validator) {
            if ($this->boolean('deliver_to_customer')
                && ! \App\Models\Shop::query()->whereKey($this->input('shop_id'))->where('offers_delivery', true)->whereNotNull('delivery_pricing_set_at')->exists()) {
                $validator->errors()->add('deliver_to_customer', 'Delivery is not available until the shop sets its delivery pricing.');
            }
        });
    }

    /** A rider has to reach the customer: collecting the laundry from them, or bringing it back when it is done. */
    private function riderInvolved(): bool
    {
        return $this->input('pickup_type') === 'pickup' || $this->boolean('deliver_to_customer');
    }

    public function rules(): array
    {
        return [
            'shop_id' => ['required', 'integer', Rule::exists('shops', 'id')],
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

            // ─── Pricing model ─────────────────────────────
            'pricing_model' => ['required', Rule::in(['per_kg', 'per_bundle', 'per_piece'])],

            // ─── Per KG fields ─────────────────────────────
            // The estimate is the customer's own figure from the online order form; staff enter the actual weight.
            'estimated_weight_kg' => [
                'nullable',
                'numeric',
                'min:0.1',
                'max:999.99',
            ],
            'actual_weight_kg' => [
                // A pickup order is weighed later, once the rider has brought the laundry to the shop.
                Rule::requiredIf(fn () => $this->input('pricing_model') === 'per_kg' && $this->input('pickup_type') !== 'pickup'),
                'nullable',
                'numeric',
                'min:0.1',
                'max:999.99',
            ],
            'price_per_kg' => [
                Rule::requiredIf(fn () => $this->input('pricing_model') === 'per_kg'),
                'nullable',
                'numeric',
                'min:0',
            ],

            // ─── Per Bundle fields ──────────────────────────
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

            // ─── Per Piece fields ───────────────────────────
            'price_per_piece' => [
                Rule::requiredIf(fn () => $this->input('pricing_model') === 'per_piece'),
                'nullable',
                'numeric',
                'min:0',
            ],
            'piece_count' => [
                Rule::requiredIf(fn () => $this->input('pricing_model') === 'per_piece'),
                'nullable',
                'integer',
                'min:1',
                'max:500',
            ],

            // ─── Common ────────────────────────────────────
            'promotion_id' => ['nullable', 'integer', Rule::exists('promotions', 'id')->where('shop_id', app(\App\Services\ShopContext::class)->shopIdFor($this->user()))],
            'additional_charges' => ['nullable', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'total_amount' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['nullable', Rule::in(ShopOrder::PAYMENT_METHODS)],
            'amount_paid' => ['nullable', 'numeric', 'min:0'],
            'estimated_completion_at' => ['nullable', 'date'],
            'customer_agreement_version' => ['required', 'string', 'max:30'],
            'customer_agreement_accepted' => ['required', 'accepted'],

            // ─── Supplies ──────────────────────────────────
            'supplies' => ['nullable', 'array'],
            'supplies.*.inventory_id' => ['required', 'integer', 'exists:inventory,id'],
            'supplies.*.quantity_used' => ['required', 'numeric', 'min:0.01'],
            'supplies.*.unit' => ['required', 'string', 'max:50'],
        ];
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
            'shop_id.exists' => 'The selected shop does not exist.',
            'supplies.*.inventory_id.exists' => 'One or more selected supplies do not exist in inventory.',
            'supplies.*.quantity_used.min' => 'Quantity used must be at least 0.01.',
            'estimated_completion_at.after' => 'Estimated completion date must be in the future.',
            'customer_agreement_accepted.accepted' => 'Confirm that the customer reviewed and accepted the laundry service agreement.',
        ];
    }
}
