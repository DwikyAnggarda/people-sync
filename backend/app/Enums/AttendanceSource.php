<?php

namespace App\Enums;

enum AttendanceSource: string
{
    case Mobile = 'mobile';
    case Manual = 'manual';

    public function label(): string
    {
        return match ($this) {
            self::Mobile => 'Mobile',
            self::Manual => 'Manual',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->toArray();
    }
}
