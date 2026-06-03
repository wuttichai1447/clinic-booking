<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\SlotAvailabilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SlotController extends Controller
{
    public function __construct(protected SlotAvailabilityService $slots) {}

    public function index(Request $request): JsonResponse
    {
        $therapistId = $request->string('therapistId')->toString();
        $date = $request->string('date')->toString();
        $clinicId = $request->string('clinicId')->toString() ?: null;

        if (! $therapistId || ! $date) {
            return response()->json([]);
        }

        return response()->json($this->slots->slotsFor($therapistId, $date, $clinicId));
    }
}
