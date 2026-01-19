<?php

namespace App\Filament\Resources\Locations\Pages;

use App\Filament\Resources\Locations\LocationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLocation extends EditRecord
{
    protected static string $resource = LocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Set location data for MapPicker component
        $data['location'] = [
            'latitude' => (float) ($data['latitude'] ?? -6.2088),
            'longitude' => (float) ($data['longitude'] ?? 106.8456),
            'radius_meters' => (int) ($data['radius_meters'] ?? 100),
        ];

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
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
