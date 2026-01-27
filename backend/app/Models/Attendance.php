<?php

namespace App\Models;

use App\Enums\AttendanceSource;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = [
        'employee_id',
        'date',
        'clock_in_at',
        'clock_out_at',
        'photo_path',
        'latitude',
        'longitude',
        'source',
        'notes',
        'is_late',
        'late_duration_minutes',
    ];

    protected $casts = [
        'date' => 'date',
        'clock_in_at' => 'datetime',
        'clock_out_at' => 'datetime',
        'source' => AttendanceSource::class,
    ];

    // protected $appends = [
    //     'is_late',
    //     'late_duration_minutes',
    // ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get attendance for a specific employee and date.
     */
    public static function forEmployeeAndDate(int $employeeId, Carbon $date): ?self
    {
        return static::where('employee_id', $employeeId)
            ->whereDate('date', $date)
            ->first();
    }

    /**
     * Check if the employee clocked in late.
     * Uses stored value if available, falls back to calculation for backward compatibility.
     */
    protected function isLate(): Attribute
    {
        return Attribute::make(
            get: function (): bool {
                // Use stored value if available (column has data)
                if ($this->attributes['is_late'] ?? null !== null) {
                    return (bool) $this->attributes['is_late'];
                }

                // Fallback to calculation for backward compatibility
                if (!$this->clock_in_at || !$this->date) {
                    return false;
                }

                $workSchedule = WorkSchedule::forDayOfWeek($this->date->dayOfWeek);

                if (!$workSchedule || !$workSchedule->is_working_day || !$workSchedule->work_start_time) {
                    return false;
                }

                $expectedStartTime = Carbon::parse($this->date->format('Y-m-d') . ' ' . $workSchedule->work_start_time->format('H:i:s'));

                return $this->clock_in_at->gt($expectedStartTime);
            }
        );
    }

    /**
     * Get the late duration in minutes.
     * Uses stored value if available, falls back to calculation for backward compatibility.
     */
    protected function lateDurationMinutes(): Attribute
    {
        return Attribute::make(
            get: function (): int {
                // Use stored value if available (column has data)
                if ($this->attributes['late_duration_minutes'] ?? null !== null) {
                    return (int) $this->attributes['late_duration_minutes'];
                }

                // Fallback to calculation for backward compatibility
                if (!$this->is_late || !$this->clock_in_at || !$this->date) {
                    return 0;
                }

                $workSchedule = WorkSchedule::forDayOfWeek($this->date->dayOfWeek);

                if (!$workSchedule || !$workSchedule->work_start_time) {
                    return 0;
                }

                $expectedStartTime = Carbon::parse($this->date->format('Y-m-d') . ' ' . $workSchedule->work_start_time->format('H:i:s'));

                return (int) $expectedStartTime->diffInMinutes($this->clock_in_at);
            }
        );
    }

    /**
     * Get formatted late duration.
     */
    public function getLateDurationFormattedAttribute(): string
    {
        $minutes = $this->late_duration_minutes;

        if ($minutes === 0) {
            return '-';
        }

        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        if ($hours > 0) {
            return "{$hours} jam {$remainingMinutes} menit";
        }

        return "{$remainingMinutes} menit";
    }

    /**
     * Check if the employee left early.
     */
    public function getIsEarlyLeaveAttribute(): bool
    {
        if (!$this->clock_out_at || !$this->date) {
            return false;
        }

        $workSchedule = WorkSchedule::forDayOfWeek($this->date->dayOfWeek);

        if (!$workSchedule || !$workSchedule->is_working_day || !$workSchedule->work_end_time) {
            return false;
        }

        $expectedEndTime = Carbon::parse($this->date->format('Y-m-d') . ' ' . $workSchedule->work_end_time->format('H:i:s'));

        return $this->clock_out_at->lt($expectedEndTime);
    }

    /**
     * Get the early leave duration in minutes.
     */
    public function getEarlyLeaveDurationMinutesAttribute(): int
    {
        if (!$this->is_early_leave || !$this->clock_out_at || !$this->date) {
            return 0;
        }

        $workSchedule = WorkSchedule::forDayOfWeek($this->date->dayOfWeek);

        if (!$workSchedule || !$workSchedule->work_end_time) {
            return 0;
        }

        $expectedEndTime = Carbon::parse($this->date->format('Y-m-d') . ' ' . $workSchedule->work_end_time->format('H:i:s'));

        return (int) $this->clock_out_at->diffInMinutes($expectedEndTime);
    }

    /**
     * Get formatted early leave duration.
     */
    public function getEarlyLeaveDurationFormattedAttribute(): string
    {
        $minutes = $this->early_leave_duration_minutes;

        if ($minutes === 0) {
            return '-';
        }

        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        if ($hours > 0) {
            return "{$hours} jam {$remainingMinutes} menit";
        }

        return "{$remainingMinutes} menit";
    }

    /**
     * Get work duration in minutes.
     */
    public function getWorkDurationMinutesAttribute(): ?int
    {
        if (!$this->clock_in_at || !$this->clock_out_at) {
            return null;
        }

        return (int) $this->clock_in_at->diffInMinutes($this->clock_out_at);
    }

    /**
     * Get formatted work duration.
     */
    public function getWorkDurationFormattedAttribute(): string
    {
        $minutes = $this->work_duration_minutes;

        if ($minutes === null) {
            return '-';
        }

        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        if ($hours > 0) {
            return "{$hours} jam {$remainingMinutes} menit";
        }

        return "{$remainingMinutes} menit";
    }
}
