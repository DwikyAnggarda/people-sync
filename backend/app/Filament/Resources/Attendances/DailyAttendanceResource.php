<?php

namespace App\Filament\Resources\Attendances;

use App\Filament\Resources\Attendances\Pages\CreateDailyAttendance;
use App\Filament\Resources\Attendances\Pages\EditDailyAttendance;
use App\Filament\Resources\Attendances\Pages\ListDailyAttendances;
use App\Filament\Resources\Attendances\Schemas\ManualAttendanceForm;
use App\Filament\Resources\Attendances\Tables\DailyAttendanceTable;
use App\Models\Attendance;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class DailyAttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static string|UnitEnum|null $navigationGroup = 'Kelola Kehadiran';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'daily-attendance';

    public static function getNavigationLabel(): string
    {
        return 'Kehadiran Harian';
    }

    public static function getModelLabel(): string
    {
        return 'Kehadiran';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Kehadiran Harian';
    }

    public static function form(Schema $schema): Schema
    {
        return ManualAttendanceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DailyAttendanceTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDailyAttendances::route('/'),
            'create' => CreateDailyAttendance::route('/create'),
            'edit' => EditDailyAttendance::route('/{record}/edit'),
        ];
    }
}
