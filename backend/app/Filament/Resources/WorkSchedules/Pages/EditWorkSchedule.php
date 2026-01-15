<?php

namespace App\Filament\Resources\WorkSchedules\Pages;

use App\Filament\Resources\WorkSchedules\WorkScheduleResource;
use Filament\Resources\Pages\EditRecord;

class EditWorkSchedule extends EditRecord
{
    protected static string $resource = WorkScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No delete action - work schedules should not be deleted
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Clear times if not a working day
        if (!$data['is_working_day']) {
            $data['work_start_time'] = null;
            $data['work_end_time'] = null;
        }

        return $data;
    }
}
