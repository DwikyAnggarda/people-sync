<?php

namespace App\Filament\Resources\Attendances;

use App\Filament\Resources\Attendances\Pages\ListAttendanceReviewDaily;
use App\Models\Attendance;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class AttendanceReviewDailyResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static string|UnitEnum|null $navigationGroup = 'Kelola Kehadiran';

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'attendance-review-daily';

    public static function getNavigationLabel(): string
    {
        return 'Review Kehadiran (Harian)';
    }

    public static function getModelLabel(): string
    {
        return 'Review Kehadiran Harian';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Review Kehadiran Harian';
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view_attendance_review_daily') ?? false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAttendanceReviewDaily::route('/'),
        ];
    }
}
