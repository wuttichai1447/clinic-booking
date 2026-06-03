<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use Illuminate\Http\JsonResponse;

class ClinicController extends Controller
{
    public function index(): JsonResponse
    {
        $clinics = Clinic::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn (Clinic $c) => [
                'id' => $c->id,
                'name' => $c->name,
                'address' => $c->address,
                'phone' => $c->phone,
                'image' => $c->image,
            ]);

        return response()->json($clinics);
    }
}
