<?php

namespace App\Filament\Resources\Attendances\Pages;

use App\Enums\AttendanceSource;
use App\Filament\Resources\Attendances\DailyAttendanceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDailyAttendances extends ListRecords
{
    protected static string $resource = DailyAttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Manual Attendance')
                ->icon('heroicon-o-plus-circle'),
        ];
    }
}
