<?php

namespace App\Models;

use App\Enums\DayOfWeek;
use Illuminate\Database\Eloquent\Model;

class WorkSchedule extends Model
{
    protected $fillable = [
        'day_of_week',
        'is_working_day',
        'work_start_time',
        'work_end_time',
    ];

    protected $casts = [
        'day_of_week' => DayOfWeek::class,
        'is_working_day' => 'boolean',
        'work_start_time' => 'datetime:H:i',
        'work_end_time' => 'datetime:H:i',
    ];

    /**
     * Get work schedule for a specific day of week.
     */
    public static function forDayOfWeek(int $dayOfWeek): ?self
    {
        return static::where('day_of_week', $dayOfWeek)->first();
    }

    /**
     * Check if a given day of week is a working day.
     */
    public static function isWorkingDay(int $dayOfWeek): bool
    {
        $schedule = static::forDayOfWeek($dayOfWeek);
        return $schedule?->is_working_day ?? false;
    }
}
