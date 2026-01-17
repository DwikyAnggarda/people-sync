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
            $data['latitude'] = $data['location']['latitude'];
            $data['longitude'] = $data['location']['longitude'];
            $data['radius_meters'] = $data['location']['radius_meters'];
            unset($data['location']);
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
