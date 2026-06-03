<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Concerns\ResolvesApiUser;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Services\AppointmentLifecycleService;
use App\Services\RefundService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppointmentLifecycleController extends Controller
{
    use ResolvesApiUser;

    public function __construct(
        protected AppointmentLifecycleService $lifecycle,
        protected RefundService $refunds,
    ) {}

    public function refundPolicy(Appointment $appointment): JsonResponse
    {
        return response()->json($this->refunds->evaluate($appointment));
    }

    public function cancel(Request $request, Appointment $appointment): JsonResponse
    {
        $this->ensureCustomerAccess($request, $appointment);

        $data = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $updated = $this->lifecycle->cancel(
                $appointment,
                $this->apiUser($request),
                $data['reason'] ?? null,
                false
            );

            return response()->json($updated->toApiArray());
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function reschedule(Request $request, Appointment $appointment): JsonResponse
    {
        $this->ensureCustomerAccess($request, $appointment);

        $data = $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'timeSlotId' => 'required|string',
        ]);

        try {
            $updated = $this->lifecycle->reschedule(
                $appointment,
                $data['date'],
                $data['timeSlotId'],
                $this->apiUser($request),
                false
            );

            return response()->json($updated->toApiArray());
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    private function ensureCustomerAccess(Request $request, Appointment $appointment): void
    {
        $user = $this->apiUser($request);
        if (! $user?->isCustomer()) {
            abort(403);
        }
        if ($appointment->user_id && $appointment->user_id !== $user->id) {
            abort(403, 'ไม่มีสิทธิ์');
        }
        if (! $appointment->user_id && $user->phone && $appointment->customer_phone !== $user->phone) {
            abort(403, 'ไม่มีสิทธิ์');
        }
    }
}
