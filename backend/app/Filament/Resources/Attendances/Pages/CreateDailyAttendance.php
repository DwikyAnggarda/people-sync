<?php

namespace App\Filament\Resources\Attendances\Pages;

use App\Enums\AttendanceSource;
use App\Filament\Resources\Attendances\DailyAttendanceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDailyAttendance extends CreateRecord
{
    protected static string $resource = DailyAttendanceResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['source'] = AttendanceSource::Manual->value;

        return $data;
    }
}
