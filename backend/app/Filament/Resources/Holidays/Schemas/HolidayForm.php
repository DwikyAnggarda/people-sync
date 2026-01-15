<?php

namespace App\Filament\Resources\Holidays\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class HolidayForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Hari Libur')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Contoh: Tahun Baru, Hari Raya Idul Fitri'),
                DatePicker::make('date')
                    ->label('Tanggal')
                    ->required()
                    ->native(false)
                    ->displayFormat('d F Y'),
                Toggle::make('is_recurring')
                    ->label('Berulang Setiap Tahun')
                    ->helperText('Jika diaktifkan, hari libur ini akan berlaku pada tanggal dan bulan yang sama setiap tahun (contoh: Tahun Baru, Hari Kemerdekaan)')
                    ->default(false),
                Textarea::make('description')
                    ->label('Keterangan')
                    ->placeholder('Keterangan tambahan (opsional)')
                    ->columnSpanFull(),
            ]);
    }
}
