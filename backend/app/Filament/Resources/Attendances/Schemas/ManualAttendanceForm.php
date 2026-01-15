<?php

namespace App\Filament\Resources\Attendances\Schemas;

use App\Enums\AttendanceSource;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ManualAttendanceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('employee_id')
                    ->relationship('employee', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->label('Karyawan'),
                DatePicker::make('date')
                    ->required()
                    ->default(now())
                    ->label('Tanggal'),
                DateTimePicker::make('clock_in_at')
                    ->required()
                    ->label('Jam Masuk')
                    ->seconds(false),
                DateTimePicker::make('clock_out_at')
                    ->nullable()
                    ->label('Jam Keluar')
                    ->seconds(false)
                    ->afterOrEqual('clock_in_at'),
                Hidden::make('source')
                    ->default(AttendanceSource::Manual->value),
                Textarea::make('notes')
                    ->label('Catatan')
                    ->placeholder('Alasan input manual kehadiran...')
                    ->columnSpanFull(),
            ]);
    }
}
