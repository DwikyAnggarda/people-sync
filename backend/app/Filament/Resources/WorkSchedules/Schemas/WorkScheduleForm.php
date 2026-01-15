<?php

namespace App\Filament\Resources\WorkSchedules\Schemas;

use App\Enums\DayOfWeek;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class WorkScheduleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Placeholder::make('day_name')
                    ->label('Hari')
                    ->content(fn ($record) => $record?->day_of_week?->label() ?? '-'),
                Toggle::make('is_working_day')
                    ->label('Hari Kerja')
                    ->helperText('Aktifkan jika hari ini adalah hari kerja')
                    ->live()
                    ->afterStateUpdated(function ($state, $set) {
                        if (!$state) {
                            $set('work_start_time', null);
                            $set('work_end_time', null);
                        }
                    }),
                TimePicker::make('work_start_time')
                    ->label('Jam Mulai Kerja')
                    ->seconds(false)
                    ->visible(fn ($get) => $get('is_working_day'))
                    ->required(fn ($get) => $get('is_working_day')),
                TimePicker::make('work_end_time')
                    ->label('Jam Selesai Kerja')
                    ->seconds(false)
                    ->visible(fn ($get) => $get('is_working_day'))
                    ->required(fn ($get) => $get('is_working_day'))
                    ->after('work_start_time'),
            ]);
    }
}
