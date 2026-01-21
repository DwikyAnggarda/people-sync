<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\HolidayResource;
use App\Models\Holiday;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    /**
     * Get list of holidays.
     */
    public function index(Request $request): JsonResponse
    {
        $year = $request->input('year', now()->year);

        $holidays = Holiday::whereYear('date', $year)
            ->orderBy('date')
            ->get();

        return $this->success(HolidayResource::collection($holidays));
    }
}
