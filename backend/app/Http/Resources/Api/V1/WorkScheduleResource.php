<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkScheduleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'day_of_week' => $this->day_of_week->value,
            'day_name' => $this->day_of_week->label(),
            'is_working_day' => $this->is_working_day,
            'work_start_time' => $this->work_start_time?->format('H:i'),
            'work_end_time' => $this->work_end_time?->format('H:i'),
        ];
    }
}
