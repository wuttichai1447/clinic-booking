<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Concerns\ResolvesApiUser;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Services\PromotionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    use ResolvesApiUser;

    public function __construct(protected PromotionService $promotions) {}

    public function validateCode(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code' => 'required|string|max:50',
            'subtotal' => 'required|integer|min:0',
        ]);

        $promotion = $this->promotions->findValidCode($data['code'], (int) $data['subtotal']);

        return response()->json($this->promotions->calculate((int) $data['subtotal'], $promotion));
    }

    public function applyToAppointment(Request $request, Appointment $appointment): JsonResponse
    {
        $this->ensureCanAccess($request, $appointment);

        if (! in_array($appointment->status, ['awaiting_payment', 'pending'], true)) {
            return response()->json(['message' => 'ไม่สามารถใช้โปรโมชั่นกับการจองนี้ได้'], 422);
        }

        $data = $request->validate(['code' => 'required|string|max:50']);

        $subtotal = $appointment->subtotal ?: $appointment->amount;
        $promotion = $this->promotions->findValidCode($data['code'], $subtotal);
        $pricing = $this->promotions->calculate($subtotal, $promotion);

        $appointment->update([
            'promotion_id' => $pricing['promotionId'],
            'subtotal' => $pricing['subtotal'],
            'discount_amount' => $pricing['discountAmount'],
            'amount' => $pricing['amount'],
        ]);

        return response()->json([
            'appointment' => $appointment->fresh()->load(['clinic', 'service', 'therapist'])->toApiArray(),
            'pricing' => $pricing,
        ]);
    }

    private function ensureCanAccess(Request $request, Appointment $appointment): void
    {
        $user = $this->apiUser($request);
        if ($user?->isCustomer() && $appointment->user_id && $appointment->user_id !== $user->id) {
            abort(403);
        }
    }
}
