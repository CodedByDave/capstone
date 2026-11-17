<?php

namespace App\Support;

/**
 * What makes two orders the same customer of a shop: the same name. Spacing and capital letters do
 * not matter ("Ramuel  Rivera" and "ramuel rivera" are one customer). A customer can give a
 * different mobile number on a later order; the order keeps the number it was placed with and the
 * customer record follows the latest one.
 */
final class CustomerIdentity
{
    public static function key(string $name): string
    {
        return self::normalizedName($name);
    }

    public static function normalizedName(string $name): string
    {
        return mb_strtolower(trim((string) preg_replace('/\s+/u', ' ', $name)));
    }

    /** A counter sale left without a name or number is not anyone's customer record. */
    public static function isAnonymous(?string $name, ?string $phone): bool
    {
        $name = self::normalizedName((string) $name);

        return ($name === '' || $name === 'walk-in customer') && trim((string) $phone) === '';
    }
}
