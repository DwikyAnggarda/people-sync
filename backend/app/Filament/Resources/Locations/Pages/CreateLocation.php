<?php

namespace App\Filament\Resources\Locations\Pages;

use App\Filament\Resources\Locations\LocationResource;
use App\Models\Location;
use Filament\Resources\Pages\CreateRecord;

class CreateLocation extends CreateRecord
{
    protected static string $resource = LocationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Extract location data from MapPicker component
        if (isset($data['location']) && is_array($data['location'])) {
            $data['latitude'] = (float) ($data['location']['latitude'] ?? -6.2088);
            $data['longitude'] = (float) ($data['location']['longitude'] ?? 106.8456);
            $data['radius_meters'] = (int) ($data['location']['radius_meters'] ?? 100);
            unset($data['location']);
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
