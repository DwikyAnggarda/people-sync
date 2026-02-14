<?php

namespace App\Filament\Resources\Overtimes\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Utilities\Get;
use Carbon\Carbon;

class OvertimeForm
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
                DatePicker::make('date')
                    ->required(),
                TimePicker::make('start_time')
                    ->required()
                    ->seconds(false)
                    ->live(onBlur: true),
                TimePicker::make('end_time')
                    ->required()
                    ->seconds(false)
                    ->live(onBlur: true),
                Placeholder::make('duration')
                    ->label('Duration')
                    ->content(function (Get $get) {
                        $start = $get('start_time');
                        $end = $get('end_time');

                        if (!$start || !$end) {
                            return '-';
                        }

                        try {
                            $startTime = Carbon::parse($start);
                            $endTime = Carbon::parse($end);

                            if ($endTime->lt($startTime)) {
                                $endTime->addDay();
                            }

                            $totalDuration = $endTime->diff($startTime);

                            return $totalDuration->format('%H Hours %I Minutes');
                        } catch (\Exception $e) {
                            return '-';
                        }
                    }),
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
