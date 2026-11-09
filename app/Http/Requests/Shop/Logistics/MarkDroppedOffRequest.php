<?php

namespace App\Http\Requests\Shop\Logistics;

use Illuminate\Foundation\Http\FormRequest;

class MarkDroppedOffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isOwner() === true || $this->user()?->isStaff() === true;
    }

    public function rules(): array
    {
        return [];
    }
}
