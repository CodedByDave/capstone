<?php

namespace App\Http\Requests\Shop;

use App\Services\ShopReportService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReportPeriodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('period')) {
            $this->merge(['period' => ShopReportService::DEFAULT_PERIOD]);
        }
    }

    public function rules(): array
    {
        return [
            'period' => ['required', Rule::in(ShopReportService::PERIODS)],
        ];
    }

    public function period(): string
    {
        return $this->validated('period');
    }
}
