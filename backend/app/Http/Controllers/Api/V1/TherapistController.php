<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Therapist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TherapistController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Therapist::query()->where('is_active', true);

        if ($request->filled('clinicId')) {
            $query->where('clinic_id', $request->string('clinicId'));
        }

        $therapists = $query->orderBy('name')->get()->map(fn (Therapist $t) => [
            'id' => $t->id,
            'name' => $t->name,
            'specialty' => $t->specialty,
            'clinicId' => $t->clinic_id,
            'image' => $this->publicImageUrl($t->image),
        ]);

        return response()->json($therapists);
    }

    private function publicImageUrl(?string $image): ?string
    {
        if (! $image) {
            return null;
        }
        if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
            return $image;
        }

        return url($image);
    }
}
