<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = [
        'name',
        'latitude',
        'longitude',
        'radius_meters',
        'address',
        'is_active',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'radius_meters' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Scope a query to only include active locations.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Check if a given coordinate is within this location's radius.
     *
     * Uses Haversine formula to calculate distance between two points.
     *
     * @param float $latitude
     * @param float $longitude
     * @return bool
     */
    public function isWithinRadius(float $latitude, float $longitude): bool
    {
        $distance = $this->calculateDistance($latitude, $longitude);
        return $distance <= $this->radius_meters;
    }

    /**
     * Calculate distance in meters from this location to given coordinates.
     *
     * Uses Haversine formula.
     *
     * @param float $latitude
     * @param float $longitude
     * @return float Distance in meters
     */
    public function calculateDistance(float $latitude, float $longitude): float
    {
        $earthRadius = 6371000; // Earth's radius in meters

        $latFrom = deg2rad($this->latitude);
        $lonFrom = deg2rad($this->longitude);
        $latTo = deg2rad($latitude);
        $lonTo = deg2rad($longitude);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(
            pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)
        ));

        return $angle * $earthRadius;
    }

    /**
     * Find the nearest location from given coordinates.
     *
     * @param float $latitude
     * @param float $longitude
     * @return static|null
     */
    public static function findNearest(float $latitude, float $longitude): ?self
    {
        $locations = static::active()->get();

        if ($locations->isEmpty()) {
            return null;
        }

        return $locations->sortBy(function ($location) use ($latitude, $longitude) {
            return $location->calculateDistance($latitude, $longitude);
        })->first();
    }

    /**
     * Find all locations that contain the given coordinates within their radius.
     *
     * @param float $latitude
     * @param float $longitude
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function findContaining(float $latitude, float $longitude)
    {
        return static::active()->get()->filter(function ($location) use ($latitude, $longitude) {
            return $location->isWithinRadius($latitude, $longitude);
        });
    }
}
