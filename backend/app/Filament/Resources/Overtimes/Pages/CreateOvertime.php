<?php

namespace App\Filament\Resources\Overtimes\Pages;

use App\Filament\Resources\Overtimes\OvertimeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOvertime extends CreateRecord
{
    protected static string $resource = OvertimeResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (isset($data['status']) && $data['status'] === 'approved') {
            $data['approved_by'] = auth()->id();
        }

        return $data;
    }
}
