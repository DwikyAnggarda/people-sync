<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Field;

class MapPicker extends Field
{
    protected string $view = 'filament.forms.components.map-picker';

    protected float $defaultLatitude = -6.2088;

    protected float $defaultLongitude = 106.8456;

    protected int $defaultZoom = 13;

    protected int $minRadius = 10;

    protected int $maxRadius = 5000;

    protected int $defaultRadius = 100;

    protected function setUp(): void
    {
        parent::setUp();

        $this->default([
            'latitude' => $this->defaultLatitude,
            'longitude' => $this->defaultLongitude,
            'radius_meters' => $this->defaultRadius,
        ]);

        $this->dehydrateStateUsing(function ($state) {
            if (is_array($state)) {
                return $state;
            }
            return [
                'latitude' => $this->defaultLatitude,
                'longitude' => $this->defaultLongitude,
                'radius_meters' => $this->defaultRadius,
            ];
        });
    }

    public function defaultLocation(float $latitude, float $longitude): static
    {
        $this->defaultLatitude = $latitude;
        $this->defaultLongitude = $longitude;

        return $this;
    }

    public function defaultZoom(int $zoom): static
    {
        $this->defaultZoom = $zoom;

        return $this;
    }

    public function radiusRange(int $min, int $max): static
    {
        $this->minRadius = $min;
        $this->maxRadius = $max;

        return $this;
    }

    public function defaultRadius(int $radius): static
    {
        $this->defaultRadius = $radius;

        return $this;
    }

    public function getDefaultLatitude(): float
    {
        return $this->defaultLatitude;
    }

    public function getDefaultLongitude(): float
    {
        return $this->defaultLongitude;
    }

    public function getDefaultZoom(): int
    {
        return $this->defaultZoom;
    }

    public function getMinRadius(): int
    {
        return $this->minRadius;
    }

    public function getMaxRadius(): int
    {
        return $this->maxRadius;
    }

    public function getDefaultRadius(): int
    {
        return $this->defaultRadius;
    }
}
