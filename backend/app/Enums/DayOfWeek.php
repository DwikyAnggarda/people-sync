<?php

namespace App\Enums;

enum DayOfWeek: int
{
    case Sunday = 0;
    case Monday = 1;
    case Tuesday = 2;
    case Wednesday = 3;
    case Thursday = 4;
    case Friday = 5;
    case Saturday = 6;

    public function label(): string
    {
        return match ($this) {
            self::Sunday => 'Minggu',
            self::Monday => 'Senin',
            self::Tuesday => 'Selasa',
            self::Wednesday => 'Rabu',
            self::Thursday => 'Kamis',
            self::Friday => 'Jumat',
            self::Saturday => 'Sabtu',
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::Sunday => 'Min',
            self::Monday => 'Sen',
            self::Tuesday => 'Sel',
            self::Wednesday => 'Rab',
            self::Thursday => 'Kam',
            self::Friday => 'Jum',
            self::Saturday => 'Sab',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->toArray();
    }
}
