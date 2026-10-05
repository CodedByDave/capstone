<?php

namespace App\Casts;

use App\Support\PhilippinePhone;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class PhilippinePhoneCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        return PhilippinePhone::format($value);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        return PhilippinePhone::format($value);
    }
}
