<?php

namespace App\Http\Requests\Driver;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Riders are authenticated by the unguessable driver token in the URL, not by a user session.
 */
class UpdateRiderLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
        ];
    }
}
