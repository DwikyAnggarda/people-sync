<?php

namespace App\Enums;

enum AttendanceStatus: string
{
    case Present = 'present';
    case Absent = 'absent';
    case OnLeave = 'on_leave';
    case Weekend = 'weekend';
    case Holiday = 'holiday';
    case NotYet = 'not_yet';

    public function label(): string
    {
        return match ($this) {
            self::Present => 'Hadir',
            self::Absent => 'Tidak Hadir',
            self::OnLeave => 'Cuti/Izin',
            self::Weekend => 'Akhir Pekan',
            self::Holiday => 'Libur',
            self::NotYet => 'Belum Tiba',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Present => 'success',
            self::Absent => 'danger',
            self::OnLeave => 'info',
            self::Weekend => 'gray',
            self::Holiday => 'warning',
            self::NotYet => 'gray',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Present => 'heroicon-o-check-circle',
            self::Absent => 'heroicon-o-x-circle',
            self::OnLeave => 'heroicon-o-calendar',
            self::Weekend => 'heroicon-o-moon',
            self::Holiday => 'heroicon-o-star',
            self::NotYet => 'heroicon-o-clock',
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::Present => 'H',
            self::Absent => 'A',
            self::OnLeave => 'C',
            self::Weekend => 'W',
            self::Holiday => 'L',
            self::NotYet => '-',
        };
    }
}
