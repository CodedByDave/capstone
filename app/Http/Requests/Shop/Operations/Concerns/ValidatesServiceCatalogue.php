<?php

namespace App\Http\Requests\Shop\Operations\Concerns;

use App\Services\ServiceCategoryService;
use App\Services\ShopContext;
use Illuminate\Validation\Rule;

/** Rules shared by the create and edit service forms for the category and the supplies the service uses. */
trait ValidatesServiceCatalogue
{
    private function shopId(): int
    {
        return app(ShopContext::class)->shopIdFor($this->user());
    }

    /** @return array<string, mixed> */
    protected function catalogueRules(): array
    {
        $shopId = $this->shopId();

        return [
            'category' => ['required', Rule::in(app(ServiceCategoryService::class)->allowedKeys($shopId))],
            'uses_supplies' => ['boolean'],
            'supplies' => [
                Rule::requiredIf(fn () => $this->boolean('uses_supplies')),
                'nullable', 'array', 'max:20',
            ],
            'supplies.*.inventory_id' => [
                'required', 'integer', 'distinct',
                Rule::exists('inventory', 'id')->where('shop_id', $shopId)->whereNull('deleted_at'),
            ],
            'supplies.*.quantity' => ['required', 'numeric', 'min:0.01', 'max:9999'],
            'supplies.*.unit' => ['required', 'string', 'max:50'],
        ];
    }

    /** @return array<string, string> */
    protected function catalogueMessages(): array
    {
        return [
            'supplies.required' => 'Add at least one supply, or turn off "Uses supplies".',
            'supplies.*.inventory_id.exists' => 'One of the selected supplies is not in your inventory.',
            'supplies.*.inventory_id.distinct' => 'Each supply can only be listed once.',
            'supplies.*.quantity.min' => 'Supply quantity must be at least 0.01.',
        ];
    }
}
