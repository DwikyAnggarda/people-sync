<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\LocationResource;
use App\Models\Location;
use Illuminate\Http\JsonResponse;

class LocationController extends Controller
{
    /**
     * Get list of active attendance locations.
     */
    public function index(): JsonResponse
    {
        $locations = Location::active()
            ->orderBy('name')
            ->get();

        return $this->success(LocationResource::collection($locations));
    }
}
