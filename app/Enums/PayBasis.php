<?php

namespace App\Enums;

enum PayBasis: string
{
    case Monthly = 'monthly';
    case Daily = 'daily';
    case Hourly = 'hourly';
    case PerShift = 'per_shift';
    case FixedContract = 'fixed_contract';

    public function label(): string
    {
        return match ($this) {
            self::Monthly => 'Monthly',
            self::Daily => 'Daily',
            self::Hourly => 'Hourly',
            self::PerShift => 'Per Shift',
            self::FixedContract => 'Fixed Contract',
        };
    }

    public static function options(): array
    {
        return array_map(
            fn (self $basis) => ['value' => $basis->value, 'label' => $basis->label()],
            self::cases(),
        );
    }
}
