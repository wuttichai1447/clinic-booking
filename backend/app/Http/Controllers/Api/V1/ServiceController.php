<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Service::query()->where('is_active', true);

        if ($request->filled('clinicId')) {
            $query->where(function ($q) use ($request) {
                $q->whereNull('clinic_id')
                    ->orWhere('clinic_id', $request->string('clinicId'));
            });
        }

        $services = $query->orderBy('name')->get()->map(fn (Service $s) => [
            'id' => $s->id,
            'name' => $s->name,
            'duration' => $s->duration,
            'price' => $s->price,
            'clinicId' => $s->clinic_id,
            'image' => $s->image,
        ]);

        return response()->json($services);
    }
}
