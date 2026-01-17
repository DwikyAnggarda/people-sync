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
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'radius_meters' => $data['radius_meters'],
        ];

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
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
