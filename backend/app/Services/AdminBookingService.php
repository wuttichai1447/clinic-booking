<?php

namespace App\Services;

use App\Jobs\SubmitAppointmentToPartnersJob;
use App\Models\Appointment;
use App\Models\Payment;
use App\Models\Service;
use App\Models\Therapist;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdminBookingService
{
    public function __construct(
        protected SlotAvailabilityService $slots,
        protected PromotionService $promotions,
        protected NotificationService $notify,
    ) {}

    /**
     * @param  array{
     *   clinic_id: string,
     *   service_id: string,
     *   therapist_id: string,
     *   date: string,
     *   time_slot_id: string,
     *   customer_name: string,
     *   customer_phone: string,
     *   customer_email?: string|null,
     *   notes?: string|null,
     *   promo_code?: string|null,
     *   payment_mode: 'counter'|'later',
     *   counter_method?: string|null,
     * }  $data
     */
    public function create(array $data): Appointment
    {
        $service = Service::findOrFail($data['service_id']);
        $therapist = Therapist::findOrFail($data['therapist_id']);

        if ($service->clinic_id !== null && $service->clinic_id !== $data['clinic_id']) {
            throw ValidationException::withMessages(['service_id' => ['บริการไม่ตรงกับคลินิกที่เลือก']]);
        }

        if ($therapist->clinic_id !== $data['clinic_id']) {
            throw ValidationException::withMessages(['therapist_id' => ['นักบำบัดไม่ตรงกับคลินิกที่เลือก']]);
        }

        $pricing = $this->resolvePricing($service, $data['promo_code'] ?? null);
        $payAtCounter = $data['payment_mode'] === 'counter';
        $counterMethod = $data['counter_method'] ?? 'cash';

        try {
            $appointment = DB::transaction(function () use ($data, $pricing, $payAtCounter, $counterMethod) {
                $this->slots->reserveSlot(
                    $data['therapist_id'],
                    $data['date'],
                    $data['time_slot_id'],
                    $data['clinic_id'],
                );
                $this->slots->ensureTimeSlotRecord($data['therapist_id'], $data['time_slot_id']);

            $appointment = Appointment::create([
                'clinic_id' => $data['clinic_id'],
                'service_id' => $data['service_id'],
                'therapist_id' => $data['therapist_id'],
                'date' => $data['date'],
                'time_slot_id' => $data['time_slot_id'],
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'customer_email' => $data['customer_email'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => $payAtCounter ? 'confirmed' : 'awaiting_payment',
                'slot_locked_until' => $payAtCounter ? null : now()->addMinutes($this->slots->lockMinutes()),
                'promotion_id' => $pricing['promotionId'],
                'subtotal' => $pricing['subtotal'],
                'discount_amount' => $pricing['discountAmount'],
                'amount' => $pricing['amount'],
                'payment_method' => $payAtCounter ? $counterMethod : null,
                'paid_at' => $payAtCounter ? now() : null,
            ]);

            if ($payAtCounter) {
                Payment::create([
                    'appointment_id' => $appointment->id,
                    'amount' => $appointment->amount,
                    'currency' => 'thb',
                    'method' => $counterMethod,
                    'status' => 'succeeded',
                    'provider' => 'counter',
                    'paid_at' => now(),
                    'metadata' => ['booked_by' => 'admin'],
                ]);

                if ($appointment->promotion_id) {
                    $appointment->promotion?->increment('used_count');
                }

                $appointment->timeSlot?->update(['available' => false]);
            } else {
                $this->slots->applySlotLock($appointment);
            }

                return $appointment;
            });
        } catch (\RuntimeException $e) {
            throw ValidationException::withMessages(['time_slot_id' => [$e->getMessage()]]);
        }

        $appointment->load(['clinic', 'service', 'therapist', 'promotion']);

        if ($payAtCounter) {
            $this->notify->paymentConfirmed($appointment);
            SubmitAppointmentToPartnersJob::dispatch($appointment->id);
        } else {
            $this->notify->bookingCreated($appointment);
        }

        return $appointment;
    }

    protected function resolvePricing(Service $service, ?string $promoCode): array
    {
        $subtotal = $service->price;
        $pricing = [
            'promotionId' => null,
            'subtotal' => $subtotal,
            'discountAmount' => 0,
            'amount' => $subtotal,
        ];

        if (! filled($promoCode)) {
            return $pricing;
        }

        $promotion = $this->promotions->findValidCode($promoCode, $subtotal);

        return $this->promotions->calculate($subtotal, $promotion);
    }
}
