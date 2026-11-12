<?php

namespace App\Services\Sms;

/** Turns the ways staff type a Philippine mobile number into the +63 form an SMS gateway expects. */
final class PhilippinePhoneNumber
{
    /** "0917 123 4567", "9171234567", "639171234567" and "+639171234567" all become "+639171234567". */
    public static function toE164(?string $number): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $number);

        $local = match (true) {
            strlen($digits) === 11 && str_starts_with($digits, '09') => substr($digits, 1),
            strlen($digits) === 10 && str_starts_with($digits, '9') => $digits,
            strlen($digits) === 12 && str_starts_with($digits, '639') => substr($digits, 2),
            default => null,
        };

        return $local === null ? null : '+63'.$local;
    }
}
