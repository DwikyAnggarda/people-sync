<?php

namespace App\Filament\Resources\WorkSchedules\Pages;

use App\Filament\Resources\WorkSchedules\WorkScheduleResource;
use Filament\Resources\Pages\ListRecords;

class ListWorkSchedules extends ListRecords
{
    protected static string $resource = WorkScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No create action - work schedules are pre-seeded
        ];
    }
}
