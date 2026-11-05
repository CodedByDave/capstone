<?php

namespace App\Http\Requests\Map;

use App\Services\MapboxService;
use Illuminate\Foundation\Http\FormRequest;

class OptimizeRouteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'stops' => ['required', 'array', 'min:1', 'max:'.MapboxService::MAX_OPTIMIZE_STOPS],
            'stops.*.lat' => ['required', 'numeric', 'between:-90,90'],
            'stops.*.lng' => ['required', 'numeric', 'between:-180,180'],
        ];
    }

    /**
     * @return list<array{lat: float, lng: float}>
     */
    public function stops(): array
    {
        return array_values(array_map(
            fn (array $stop): array => ['lat' => (float) $stop['lat'], 'lng' => (float) $stop['lng']],
            $this->validated('stops'),
        ));
    }
}
