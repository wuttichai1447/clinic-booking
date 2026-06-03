<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Concerns\ResolvesApiUser;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Promotion;
use App\Models\Service;
use App\Models\TimeSlot;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\PaymentService;
use App\Services\PromotionService;
use App\Services\SlotAvailabilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AppointmentController extends Controller
{
    use ResolvesApiUser;

    public function __construct(
        protected PromotionService $promotions,
        protected PaymentService $payments,
        protected SlotAvailabilityService $slots,
        protected NotificationService $notify,
    ) {}

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'clinicId' => 'required|string|exists:clinics,id',
            'serviceId' => 'required|string|exists:services,id',
            'therapistId' => 'required|string|exists:therapists,id',
            'date' => 'required|date|after_or_equal:today',
            'timeSlotId' => 'required|string',
            'customerName' => 'required|string|max:255',
            'customerPhone' => 'required|string|max:20',
            'customerEmail' => 'nullable|email',
            'notes' => 'nullable|string|max:1000',
            'promoCode' => 'nullable|string|max:50',
            'pdpaAccepted' => 'sometimes|boolean',
        ]);

        /** @var User|null $user */
        $user = $this->apiUser($request);
        if ($user?->isCustomer()) {
            $data['customerName'] = $user->name;
            $data['customerPhone'] = $user->phone ?? $data['customerPhone'];
            $data['customerEmail'] = $user->email;
            if (! $user->pdpa_accepted_at && empty($data['pdpaAccepted'])) {
                throw ValidationException::withMessages([
                    'pdpaAccepted' => ['กรุณายอมรับนโยบายความเป็นส่วนตัว (PDPA)'],
                ]);
            }
            if (! empty($data['pdpaAccepted'])) {
                $user->update(['pdpa_accepted_at' => now()]);
            }
        } elseif (empty($data['pdpaAccepted'])) {
            throw ValidationException::withMessages([
                'pdpaAccepted' => ['กรุณายอมรับนโยบายความเป็นส่วนตัว (PDPA)'],
            ]);
        }

        $service = Service::findOrFail($data['serviceId']);

        $subtotal = $service->price;
        $pricing = [
            'promotionId' => null,
            'subtotal' => $subtotal,
            'discountAmount' => 0,
            'amount' => $subtotal,
        ];

        if (! empty($data['promoCode'])) {
            $promotion = $this->promotions->findValidCode($data['promoCode'], $subtotal);
            $pricing = $this->promotions->calculate($subtotal, $promotion);
        }

        if (! $this->payments->devModeEnabled() && $pricing['amount'] < PaymentService::STRIPE_THB_MIN_AMOUNT) {
            throw ValidationException::withMessages([
                'promoCode' => [
                    'ยอดหลังส่วนลดต้องไม่ต่ำกว่า ฿'.PaymentService::STRIPE_THB_MIN_AMOUNT.' (สำหรับชำระบัตร/Stripe)',
                ],
            ]);
        }

        try {
            $appointment = DB::transaction(function () use ($data, $user, $pricing) {
                $this->slots->reserveSlot(
                    $data['therapistId'],
                    $data['date'],
                    $data['timeSlotId'],
                    $data['clinicId'],
                );
                $this->slots->ensureTimeSlotRecord($data['therapistId'], $data['timeSlotId']);

            $appointment = Appointment::create([
                'user_id' => $user?->isCustomer() ? $user->id : null,
                'clinic_id' => $data['clinicId'],
                'service_id' => $data['serviceId'],
                'therapist_id' => $data['therapistId'],
                'date' => $data['date'],
                'time_slot_id' => $data['timeSlotId'],
                'customer_name' => $data['customerName'],
                'customer_phone' => $data['customerPhone'],
                'customer_email' => $data['customerEmail'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => 'awaiting_payment',
                'slot_locked_until' => now()->addMinutes($this->slots->lockMinutes()),
                'promotion_id' => $pricing['promotionId'],
                'subtotal' => $pricing['subtotal'],
                'discount_amount' => $pricing['discountAmount'],
                'amount' => $pricing['amount'],
            ]);

            $this->slots->applySlotLock($appointment);

                return $appointment;
            });
        } catch (\RuntimeException $e) {
            throw ValidationException::withMessages(['timeSlotId' => [$e->getMessage()]]);
        }

        $appointment->load('promotion');
        $this->notify->bookingCreated($appointment);

        return response()->json($appointment->toApiArray(), 201);
    }

    /** รายการจองของลูกค้าที่ล็อกอินแล้ว */
    public function mine(Request $request): JsonResponse
    {
        $user = $this->apiUser($request);
        if (! $user?->isCustomer()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $this->linkOrphanAppointmentsToUser($user);

        $appointments = Appointment::query()
            ->where('user_id', $user->id)
            ->with(['clinic', 'service', 'therapist', 'promotion'])
            ->orderByDesc('created_at')
            ->get()
            ->map->toApiArray();

        return response()->json($appointments);
    }

    /** ค้นหาด้วยเบอร์ (ไม่ต้องล็อกอิน — รองรับการจองแบบเดิม) */
    public function index(Request $request): JsonResponse
    {
        $request->validate(['phone' => 'required|string']);

        $appointments = Appointment::query()
            ->where('customer_phone', $request->string('phone'))
            ->with(['clinic', 'service', 'therapist'])
            ->orderByDesc('created_at')
            ->get()
            ->map->toApiArray();

        return response()->json($appointments);
    }

    public function show(Request $request, Appointment $appointment): JsonResponse
    {
        $this->ensureCanAccess($request, $appointment);

        return response()->json($appointment->load(['clinic', 'service', 'therapist', 'promotion'])->toApiArray());
    }

    /** เอกสารสรุปก่อนชำระเงิน (Stripe / ใบสรุป) */
    public function invoice(Request $request, Appointment $appointment): JsonResponse
    {
        $this->ensureCanAccess($request, $appointment);

        $appointment->load(['clinic', 'service', 'therapist', 'promotion']);

        $subtotal = $appointment->subtotal ?: $appointment->amount;
        $discount = $appointment->discount_amount;
        $total = $appointment->amount;

        return response()->json([
            'invoiceNumber' => $appointment->id,
            'issuedAt' => now()->toIso8601String(),
            'customer' => [
                'name' => $appointment->customer_name,
                'phone' => $appointment->customer_phone,
                'email' => $appointment->customer_email,
            ],
            'booking' => $appointment->toApiArray(),
            'lineItems' => [
                [
                    'description' => $appointment->service?->name ?? 'บริการ',
                    'amount' => $subtotal,
                ],
                ...($discount > 0 ? [[
                    'description' => 'ส่วนลด '.($appointment->promotion?->code ?? ''),
                    'amount' => -$discount,
                ]] : []),
            ],
            'subtotal' => $subtotal,
            'discountAmount' => $discount,
            'total' => $total,
            'currency' => 'THB',
            'paymentNote' => $this->payments->stripeReady()
                ? 'ชำระด้วยบัตรผ่าน Stripe — ยอดสุทธิ ฿'.number_format($total)
                : 'โอน/พร้อมเพย์: ส่งหลักฐานแล้วรอแอดมินยืนยัน — ยอดสุทธิ ฿'.number_format($total),
            'stripeReady' => $this->payments->stripeReady(),
        ]);
    }

    private function ensureCanAccess(Request $request, Appointment $appointment): void
    {
        $user = $this->apiUser($request);
        if ($user?->isCustomer() && $appointment->user_id && $appointment->user_id !== $user->id) {
            abort(403, 'ไม่มีสิทธิ์เข้าถึงการจองนี้');
        }
    }
}
