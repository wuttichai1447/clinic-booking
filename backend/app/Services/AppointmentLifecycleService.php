<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AppointmentLifecycleService
{
    public function __construct(
        protected RefundService $refunds,
        protected SlotAvailabilityService $slots,
        protected NotificationService $notify,
        protected AuditService $audit,
    ) {}

    public function cancel(Appointment $appointment, ?User $actor, ?string $reason = null, bool $byAdmin = false): Appointment
    {
        if (in_array($appointment->status, ['cancelled', 'completed'], true)) {
            throw new \RuntimeException('การจองนี้ยกเลิกหรือเสร็จสิ้นแล้ว');
        }

        $refund = ['eligible' => false, 'amount' => 0, 'policy' => 'none'];
        if ($appointment->status === 'confirmed' || $appointment->paid_at) {
            $refund = $this->refunds->evaluate($appointment);
        }

        DB::transaction(function () use ($appointment, $reason, $refund) {
            $appointment->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancel_reason' => $reason,
                'refund_status' => $refund['eligible'] ? $refund['policy'] : 'not_eligible',
                'refund_amount' => $refund['amount'],
                'slot_locked_until' => null,
            ]);
            $appointment->timeSlot?->update(['available' => true]);
        });

        $appointment = $appointment->fresh()->load(['clinic', 'service', 'therapist', 'promotion']);
        $this->notify->appointmentCancelled($appointment);
        $this->audit->log('appointment.cancelled', $actor, $appointment, null, [
            'by_admin' => $byAdmin,
            'refund' => $refund,
        ]);

        return $appointment;
    }

    public function reschedule(
        Appointment $appointment,
        string $newDate,
        string $newTimeSlotId,
        ?User $actor = null,
        bool $byAdmin = false
    ): Appointment {
        if (! in_array($appointment->status, ['awaiting_payment', 'awaiting_verification', 'confirmed'], true)) {
            throw new \RuntimeException('ไม่สามารถเลื่อนนัดในสถานะนี้ได้');
        }

        $fromDate = $appointment->date->format('Y-m-d');
        $fromSlot = $appointment->time_slot_id;

        DB::transaction(function () use ($appointment, $newDate, $newTimeSlotId, $fromDate, $fromSlot) {
            $this->slots->reserveSlot(
                $appointment->therapist_id,
                $newDate,
                $newTimeSlotId,
                $appointment->clinic_id,
                $appointment->id,
            );

            $this->slots->ensureTimeSlotRecord($appointment->therapist_id, $newTimeSlotId);

            $appointment->update([
                'rescheduled_from_date' => $fromDate,
                'rescheduled_from_time_slot_id' => $fromSlot,
                'date' => $newDate,
                'time_slot_id' => $newTimeSlotId,
                'slot_locked_until' => now()->addMinutes($this->slots->lockMinutes()),
            ]);
        });

        $appointment = $appointment->fresh()->load(['clinic', 'service', 'therapist', 'promotion']);
        $this->notify->appointmentRescheduled($appointment);
        $this->audit->log('appointment.rescheduled', $actor, $appointment, null, [
            'by_admin' => $byAdmin,
            'from' => ['date' => $fromDate, 'slot' => $fromSlot],
            'to' => ['date' => $newDate, 'slot' => $newTimeSlotId],
        ]);

        return $appointment;
    }
}
