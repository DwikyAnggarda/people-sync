<?php

namespace App\Filament\Resources\WorkSchedules\Tables;

use App\Enums\DayOfWeek;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WorkSchedulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('day_of_week')
                    ->label('Hari')
                    ->formatStateUsing(fn ($state) => $state?->label() ?? '-')
                    ->sortable(),
                IconColumn::make('is_working_day')
                    ->label('Hari Kerja')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                TextColumn::make('work_start_time')
                    ->label('Jam Mulai')
                    ->time('H:i')
                    ->placeholder('-'),
                TextColumn::make('work_end_time')
                    ->label('Jam Selesai')
                    ->time('H:i')
                    ->placeholder('-'),
                TextColumn::make('work_duration')
                    ->label('Durasi Kerja')
                    ->state(function ($record) {
                        if (!$record->is_working_day || !$record->work_start_time || !$record->work_end_time) {
                            return '-';
                        }
                        
                        $start = \Carbon\Carbon::parse($record->work_start_time);
                        $end = \Carbon\Carbon::parse($record->work_end_time);
                        $diff = $start->diff($end);
                        
                        return $diff->format('%H jam %I menit');
                    }),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->paginated(false)
            ->defaultSort('day_of_week', 'asc');
    }
}
