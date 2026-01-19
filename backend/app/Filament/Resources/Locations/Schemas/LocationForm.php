<?php

namespace App\Filament\Resources\Locations\Schemas;

use App\Filament\Forms\Components\MapPicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LocationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Lokasi')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Lokasi')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Kantor Pusat Jakarta'),
                        Textarea::make('address')
                            ->label('Alamat')
                            ->placeholder('Alamat lengkap lokasi (opsional)')
                            ->rows(2),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->helperText('Lokasi yang tidak aktif tidak akan digunakan untuk validasi kehadiran')
                            ->default(true),
                    ])
                    ->columns(1),

                Section::make('Peta & Radius')
                    ->description('Klik pada peta atau cari alamat untuk menentukan titik lokasi, lalu atur radius cakupan.')
                    ->schema([
                        MapPicker::make('location')
                            ->label('')
                            ->required()
                            ->defaultLocation(-6.2088, 106.8456)
                            ->defaultZoom(13)
                            ->radiusRange(10, 5000)
                            ->defaultRadius(100),
                    ]),
            ]);
    }
}
