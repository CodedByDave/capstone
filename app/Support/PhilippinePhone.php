<?php

namespace App\Support;

class PhilippinePhone
{
    public static function mobileDigits(mixed $value): ?string
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        $digits = preg_replace('/\D+/', '', (string) $value);

        if (str_starts_with($digits, '63')) {
            $digits = substr($digits, 2);
        } elseif (str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }

        return preg_match('/^9\d{9}$/', $digits) === 1 ? $digits : null;
    }

    public static function format(mixed $value): ?string
    {
        $digits = self::mobileDigits($value);

        if ($digits === null) {
            return $value === null || trim((string) $value) === ''
                ? null
                : (string) $value;
        }

        return '+63-'.substr($digits, 0, 3)
            .'-'.substr($digits, 3, 4)
            .'-'.substr($digits, 7, 3);
    }

    public static function e164(mixed $value): ?string
    {
        $digits = self::mobileDigits($value);

        return $digits === null ? null : '+63'.$digits;
    }

    public static function isValid(mixed $value): bool
    {
        return self::mobileDigits($value) !== null;
    }
}
