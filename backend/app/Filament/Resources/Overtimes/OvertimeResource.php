<?php

namespace App\Filament\Resources\Overtimes;

use App\Filament\Resources\Overtimes\Pages\CreateOvertime;
use App\Filament\Resources\Overtimes\Pages\EditOvertime;
use App\Filament\Resources\Overtimes\Pages\ListOvertimes;
use App\Filament\Resources\Overtimes\Schemas\OvertimeForm;
use App\Filament\Resources\Overtimes\Tables\OvertimesTable;
use App\Models\Overtime;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OvertimeResource extends Resource
{
    protected static ?string $model = Overtime::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return OvertimeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OvertimesTable::configure($table);
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
            'index' => ListOvertimes::route('/'),
            'create' => CreateOvertime::route('/create'),
            'edit' => EditOvertime::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
