<?php

namespace App\Filament\Resources\Attendances;

use App\Filament\Resources\Attendances\Pages\ListAttendanceReviewMonthly;
use App\Models\Attendance;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class AttendanceReviewMonthlyResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTableCells;

    protected static string|UnitEnum|null $navigationGroup = 'Kelola Kehadiran';

    protected static ?int $navigationSort = 3;

    protected static ?string $slug = 'attendance-review-monthly';

    public static function getNavigationLabel(): string
    {
        return 'Review Kehadiran (Bulanan)';
    }

    public static function getModelLabel(): string
    {
        return 'Review Kehadiran Bulanan';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Review Kehadiran Bulanan';
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('view_attendance_review_monthly') ?? false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAttendanceReviewMonthly::route('/'),
        ];
    }
}
