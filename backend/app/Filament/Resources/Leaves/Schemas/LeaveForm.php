<?php

namespace App\Filament\Resources\Leaves\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

class LeaveForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('employee_id')
                    ->label('Employee')
                    ->options(fn() => \App\Models\Employee::query()
                        ->where('status', 'active')
                        ->orderBy('name')
                        ->pluck('name', 'id'))
                    ->required()
                    ->searchable(),
                Select::make('type')
                    ->options([
                        'Cuti' => 'Cuti',
                        'Jalan Dinas' => 'Jalan Dinas',
                        'Izin' => 'Izin',
                        'Sakit' => 'Sakit',
                        'WFA' => 'WFA',
                    ])
                    ->required(),
                DatePicker::make('start_date')
                    ->required(),
                DatePicker::make('end_date')
                    ->required()
                    ->afterOrEqual('start_date'),
                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->default('pending')
                    ->required(),
                Textarea::make('reason')
                    ->columnSpanFull(),
                Placeholder::make('approved_by_name')
                    ->label('Approved By')
                    ->content(fn($record) => $record?->approver?->name ?? '-'),
            ]);
    }
}
