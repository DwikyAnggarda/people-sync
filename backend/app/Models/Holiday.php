<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    protected $fillable = [
        'name',
        'date',
        'is_recurring',
        'description',
    ];

    protected $casts = [
        'date' => 'date',
        'is_recurring' => 'boolean',
    ];

    /**
     * Check if a given date is a holiday.
     */
    public static function isHoliday(Carbon $date): bool
    {
        return static::forDate($date) !== null;
    }

    /**
     * Get the holiday for a given date.
     */
    public static function forDate(Carbon $date): ?self
    {
        // Check for exact date match
        $holiday = static::whereDate('date', $date)->first();

        if ($holiday) {
            return $holiday;
        }

        // Check for recurring holidays (same month and day, any year)
        return static::where('is_recurring', true)
            ->whereMonth('date', $date->month)
            ->whereDay('date', $date->day)
            ->first();
    }
}
