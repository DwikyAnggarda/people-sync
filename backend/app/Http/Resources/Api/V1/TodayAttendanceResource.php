<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TodayAttendanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'date' => $this->date->format('Y-m-d'),
            'clock_in_at' => $this->clock_in_at?->toIso8601String(),
            'clock_out_at' => $this->clock_out_at?->toIso8601String(),
            'is_late' => $this->is_late,
            'late_duration_minutes' => $this->late_duration_minutes,
            'late_duration_formatted' => $this->late_duration_formatted,
            'is_early_leave' => $this->is_early_leave,
            'work_duration_minutes' => $this->work_duration_minutes,
            'work_duration_formatted' => $this->work_duration_formatted,
            'source' => $this->source?->value,
            'can_clock_in' => $this->clock_in_at === null,
            'can_clock_out' => $this->clock_in_at !== null && $this->clock_out_at === null,
        ];
    }
}
