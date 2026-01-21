<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\WorkScheduleResource;
use App\Models\WorkSchedule;
use Illuminate\Http\JsonResponse;

class WorkScheduleController extends Controller
{
    /**
     * Get work schedule configuration.
     */
    public function index(): JsonResponse
    {
        $schedules = WorkSchedule::orderBy('day_of_week')->get();

        return $this->success(WorkScheduleResource::collection($schedules));
    }
}
