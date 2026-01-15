<?php

namespace App\Filament\Resources\Holidays\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class HolidaysTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Hari Libur')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d F Y')
                    ->sortable(),
                IconColumn::make('is_recurring')
                    ->label('Berulang')
                    ->boolean()
                    ->trueIcon('heroicon-o-arrow-path')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('success')
                    ->falseColor('gray'),
                TextColumn::make('description')
                    ->label('Keterangan')
                    ->limit(50)
                    ->placeholder('-'),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('is_recurring')
                    ->label('Tipe')
                    ->options([
                        '1' => 'Berulang Setiap Tahun',
                        '0' => 'Sekali Saja',
                    ]),
                Filter::make('upcoming')
                    ->label('Akan Datang')
                    ->query(fn (Builder $query): Builder => $query->whereDate('date', '>=', now()))
                    ->toggle(),
                Filter::make('current_year')
                    ->label('Tahun Ini')
                    ->query(fn (Builder $query): Builder => $query->whereYear('date', now()->year))
                    ->default(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('date', 'asc');
    }
}
