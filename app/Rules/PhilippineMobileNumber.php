<?php

namespace App\Rules;

use App\Support\PhilippinePhone;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PhilippineMobileNumber implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! PhilippinePhone::isValid($value)) {
            $fail('Enter a valid Philippine mobile number, such as +63-912-3456-789.');
        }
    }
}
