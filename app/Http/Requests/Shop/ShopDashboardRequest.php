<?php

namespace App\Http\Requests\Shop;

use App\Services\ShopDashboardService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShopDashboardRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Who may open the dashboard is decided by the route middleware.
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (! in_array($this->input('period'), ShopDashboardService::PERIODS, true)) {
            $this->merge(['period' => ShopDashboardService::PERIOD_TODAY]);
        }
    }

    public function rules(): array
    {
        return [
            'period' => ['required', Rule::in(ShopDashboardService::PERIODS)],
        ];
    }
}
