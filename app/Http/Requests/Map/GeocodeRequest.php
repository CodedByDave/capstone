<?php

namespace App\Http\Requests\Map;

use Illuminate\Foundation\Http\FormRequest;

class GeocodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Open to guests (shop registration picks a location before login); throttled by the route.
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'q' => ['required', 'string', 'min:3', 'max:200'],
            'lat' => ['nullable', 'numeric', 'between:-90,90', 'required_with:lng'],
            'lng' => ['nullable', 'numeric', 'between:-180,180', 'required_with:lat'],
        ];
    }
}
