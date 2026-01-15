<?php

namespace App\Filament\Resources\WorkSchedules;

use App\Filament\Resources\WorkSchedules\Pages\EditWorkSchedule;
use App\Filament\Resources\WorkSchedules\Pages\ListWorkSchedules;
use App\Filament\Resources\WorkSchedules\Schemas\WorkScheduleForm;
use App\Filament\Resources\WorkSchedules\Tables\WorkSchedulesTable;
use App\Models\WorkSchedule;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class WorkScheduleResource extends Resource
{
    protected static ?string $model = WorkSchedule::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static string|UnitEnum|null $navigationGroup = 'Kelola Kehadiran';

    protected static ?int $navigationSort = 5;

    public static function getNavigationLabel(): string
    {
        return 'Jadwal Kerja';
    }

    public static function getModelLabel(): string
    {
        return 'Jadwal Kerja';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Jadwal Kerja';
    }

    public static function form(Schema $schema): Schema
    {
        return WorkScheduleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WorkSchedulesTable::configure($table);
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
            'index' => ListWorkSchedules::route('/'),
            'edit' => EditWorkSchedule::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        // Work schedules are pre-seeded (7 days), no need to create new ones
        return false;
    }

    public static function canDelete($record): bool
    {
        // Work schedules should not be deleted
        return false;
    }
}
