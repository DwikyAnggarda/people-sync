<?php

namespace App\Filament\Resources\Attendances\Pages;

use App\Filament\Resources\Attendances\DailyAttendanceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDailyAttendance extends EditRecord
{
    protected static string $resource = DailyAttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
