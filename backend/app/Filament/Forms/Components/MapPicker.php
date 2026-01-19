<?php

namespace App\Filament\Forms\Components;

use Closure;
use Filament\Forms\Components\Field;

class MapPicker extends Field
{
    protected string $view = 'filament.forms.components.map-picker';

    protected float|Closure $defaultLatitude = -6.2088;

    protected float|Closure $defaultLongitude = 106.8456;

    protected int|Closure $defaultZoom = 13;

    protected int|Closure $minRadius = 10;

    protected int|Closure $maxRadius = 5000;

    protected int|Closure $defaultRadius = 100;

    protected function setUp(): void
    {
        parent::setUp();

        $this->default(fn (): array => [
            'latitude' => $this->evaluate($this->defaultLatitude),
            'longitude' => $this->evaluate($this->defaultLongitude),
            'radius_meters' => $this->evaluate($this->defaultRadius),
        ]);

        $this->afterStateHydrated(function (MapPicker $component, $state): void {
            if (is_array($state) && isset($state['latitude'], $state['longitude'])) {
                return;
            }

            $component->state([
                'latitude' => $this->evaluate($this->defaultLatitude),
                'longitude' => $this->evaluate($this->defaultLongitude),
                'radius_meters' => $this->evaluate($this->defaultRadius),
            ]);
        });

        $this->dehydrateStateUsing(function ($state): ?array {
            if (!is_array($state)) {
                return null;
            }

            return [
                'latitude' => (float) ($state['latitude'] ?? $this->evaluate($this->defaultLatitude)),
                'longitude' => (float) ($state['longitude'] ?? $this->evaluate($this->defaultLongitude)),
                'radius_meters' => (int) ($state['radius_meters'] ?? $this->evaluate($this->defaultRadius)),
            ];
        });
    }

    public function defaultLocation(float|Closure $latitude, float|Closure $longitude): static
    {
        $this->defaultLatitude = $latitude;
        $this->defaultLongitude = $longitude;

        return $this;
    }

    public function defaultZoom(int|Closure $zoom): static
    {
        $this->defaultZoom = $zoom;

        return $this;
    }

    public function radiusRange(int|Closure $min, int|Closure $max): static
    {
        $this->minRadius = $min;
        $this->maxRadius = $max;

        return $this;
    }

    public function defaultRadius(int|Closure $radius): static
    {
        $this->defaultRadius = $radius;

        return $this;
    }

    public function getDefaultLatitude(): float
    {
        return $this->evaluate($this->defaultLatitude);
    }

    public function getDefaultLongitude(): float
    {
        return $this->evaluate($this->defaultLongitude);
    }

    public function getDefaultZoom(): int
    {
        return $this->evaluate($this->defaultZoom);
    }

    public function getMinRadius(): int
    {
        return $this->evaluate($this->minRadius);
    }

    public function getMaxRadius(): int
    {
        return $this->evaluate($this->maxRadius);
    }

    public function getDefaultRadius(): int
    {
        return $this->evaluate($this->defaultRadius);
    }
}
