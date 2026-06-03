<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Concerns\ResolvesApiUser;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    use ResolvesApiUser;

    public function __construct(protected PaymentService $payments) {}

    public function config(): JsonResponse
    {
        return response()->json($this->payments->config());
    }

    public function createIntent(Request $request, Appointment $appointment): JsonResponse
    {
        $this->ensureCanAccess($request, $appointment);

        $message = match ($appointment->status) {
            'awaiting_verification' => 'ส่งหลักฐานโอน/พร้อมเพย์แล้ว — รอแอดมินยืนยัน (ไม่ต้องชำระด้วยบัตรซ้ำ)',
            'confirmed', 'completed' => 'การจองนี้ชำระเงินแล้ว',
            'cancelled' => 'การจองนี้ถูกยกเลิกแล้ว',
            default => null,
        };

        if ($message !== null) {
            return response()->json(['message' => $message], 422);
        }

        if (! in_array($appointment->status, ['awaiting_payment', 'pending'], true)) {
            return response()->json(['message' => 'ไม่สามารถชำระเงินได้ในสถานะ '.$appointment->status], 422);
        }

        $data = $request->validate([
            'method' => 'required|in:credit_card,transfer,promptpay',
        ]);

        try {
            $result = $this->payments->createIntent($appointment, $data['method']);

            return response()->json($result);
        } catch (\InvalidArgumentException|\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function confirm(Request $request, Appointment $appointment): JsonResponse
    {
        $this->ensureCanAccess($request, $appointment);

        $data = $request->validate([
            'paymentIntentId' => 'required|string',
        ]);

        try {
            $appointment = $this->payments->confirmStripe($appointment, $data['paymentIntentId']);

            return response()->json($appointment->toApiArray());
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function submitManual(Request $request, Appointment $appointment): JsonResponse
    {
        $this->ensureCanAccess($request, $appointment);

        $data = $request->validate([
            'method' => 'required|in:transfer,promptpay',
            'reference' => 'required|string|min:4|max:100',
            'proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $proofPath = null;
        if ($request->hasFile('proof')) {
            $proofPath = $request->file('proof')->store('payment-proofs', 'public');
        }

        try {
            $appointment = $this->payments->submitManual(
                $appointment,
                $data['method'],
                $data['reference'],
                $proofPath
            );

            return response()->json($appointment->toApiArray());
        } catch (\InvalidArgumentException|\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function devComplete(Request $request, Appointment $appointment): JsonResponse
    {
        $this->ensureCanAccess($request, $appointment);

        $data = $request->validate([
            'method' => 'required|in:credit_card,transfer,promptpay',
        ]);

        $appointment = $this->payments->devComplete($appointment, $data['method']);

        return response()->json($appointment->load(['clinic', 'service', 'therapist', 'promotion'])->toApiArray());
    }

    private function ensureCanAccess(Request $request, Appointment $appointment): void
    {
        $user = $this->apiUser($request);
        if ($user?->isCustomer() && $appointment->user_id && $appointment->user_id !== $user->id) {
            abort(403, 'ไม่มีสิทธิ์เข้าถึงการจองนี้');
        }
    }
}
