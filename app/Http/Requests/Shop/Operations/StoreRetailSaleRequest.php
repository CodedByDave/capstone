<?php

namespace App\Http\Requests\Shop\Operations;

use App\Models\ShopOrder;
use App\Rules\PhilippineMobileNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** A retail-products-only sale recorded at the counter. Prices and stock are checked by the service. */
class StoreRetailSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Staff reach this through the Operations create permission on the route.
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'customer_name' => filled($this->customer_name) ? trim((string) $this->customer_name) : null,
            'amount_tendered' => $this->filled('amount_tendered') ? $this->amount_tendered : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'retail_items' => ['required', 'array', 'min:1', 'max:50'],
            'retail_items.*.id' => ['required', 'integer'],
            'retail_items.*.count' => ['required', 'integer', 'min:1', 'max:999'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:20', new PhilippineMobileNumber],
            // Optional: the customer is emailed nothing here; it is kept on the sale for the record.
            'customer_email' => ['nullable', 'email:rfc', 'max:255'],
            'payment_method' => ['required', Rule::in(ShopOrder::PAYMENT_METHODS)],
            'amount_tendered' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
        ];
    }

    public function messages(): array
    {
        return [
            'retail_items.required' => 'Add at least one product to the sale.',
            'retail_items.min' => 'Add at least one product to the sale.',
            'payment_method.required' => 'Choose how the customer paid.',
        ];
    }
}
