<?php

namespace App\Filament\Resources\Departments\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DepartmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                // Select::make('parent_id')
                //     ->label('Parent Department')
                //     ->relationship('parent', 'name', modifyQueryUsing: fn (Builder $query, ?Model $record) => 
                //         $record ? $query->where('id', '!=', $record->id) : $query
                //     )
                //     ->searchable()
                //     ->preload(),
            ]);
    }
}
