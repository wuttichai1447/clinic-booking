<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\ClinicHoliday;
use App\Models\StaffLeave;
use App\Models\TimeSlot;
use Carbon\Carbon;

class SlotAvailabilityService
{
    protected array $defaultTimes = [
        '09:00' => true, '09:30' => true, '10:00' => true, '10:30' => false,
        '11:00' => true, '11:30' => true, '13:00' => true, '13:30' => true,
        '14:00' => false, '14:30' => true, '15:00' => true, '15:30' => true,
        '16:00' => true, '16:30' => true, '17:00' => true,
    ];

    public function isHoliday(?string $clinicId, string $date): bool
    {
        return ClinicHoliday::query()
            ->whereDate('date', $date)
            ->where(function ($q) use ($clinicId) {
                $q->whereNull('clinic_id');
                if ($clinicId) {
                    $q->orWhere('clinic_id', $clinicId);
                }
            })
            ->exists();
    }

    public function isOnLeave(string $therapistId, string $date): bool
    {
        return StaffLeave::query()
            ->where('therapist_id', $therapistId)
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->exists();
    }

    public function slotsFor(string $therapistId, string $date, ?string $clinicId = null): array
    {
        if ($this->isHoliday($clinicId, $date) || $this->isOnLeave($therapistId, $date)) {
            return [];
        }

        $booked = Appointment::query()
            ->where('therapist_id', $therapistId)
            ->whereDate('date', $date)
            ->where(function ($q) {
                $q->whereIn('status', ['pending', 'awaiting_payment', 'awaiting_verification', 'confirmed'])
                    ->orWhere(function ($q2) {
                        $q2->where('status', 'awaiting_payment')
                            ->where('slot_locked_until', '>', now());
                    });
            })
            ->pluck('time_slot_id')
            ->all();

        return collect($this->defaultTimes)->map(function ($available, $time) use ($booked) {
            $id = str_replace(':', '-', $time);

            return [
                'id' => $id,
                'time' => $time,
                'available' => $available && ! in_array($id, $booked, true),
            ];
        })->values()->all();
    }

    public function assertSlotAvailable(string $therapistId, string $date, string $timeSlotId, ?string $clinicId = null, ?string $exceptAppointmentId = null): void
    {
        $this->assertSlotRules($therapistId, $date, $timeSlotId, $clinicId);

        if ($this->conflictingBookingsQuery($therapistId, $date, $timeSlotId, $exceptAppointmentId)->exists()) {
            throw new \RuntimeException('ช่วงเวลานี้ถูกจองแล้ว');
        }
    }

    /**
     * เรียกภายใน DB::transaction() เพื่อ lock แถวและกัน race condition
     */
    public function reserveSlot(string $therapistId, string $date, string $timeSlotId, ?string $clinicId = null, ?string $exceptAppointmentId = null): void
    {
        $this->assertSlotRules($therapistId, $date, $timeSlotId, $clinicId);

        $conflicts = $this->conflictingBookingsQuery($therapistId, $date, $timeSlotId, $exceptAppointmentId)
            ->lockForUpdate()
            ->get();

        if ($conflicts->isNotEmpty()) {
            throw new \RuntimeException('ช่วงเวลานี้ถูกจองแล้ว');
        }
    }

    protected function assertSlotRules(string $therapistId, string $date, string $timeSlotId, ?string $clinicId): void
    {
        if ($this->isHoliday($clinicId, $date)) {
            throw new \RuntimeException('วันที่เลือกเป็นวันหยุดของคลินิก');
        }

        if ($this->isOnLeave($therapistId, $date)) {
            throw new \RuntimeException('นักบำบัดลาในวันที่เลือก');
        }

        if (! isset($this->defaultTimes[str_replace('-', ':', $timeSlotId)])) {
            throw new \RuntimeException('ช่วงเวลาไม่ถูกต้อง');
        }
    }

    protected function conflictingBookingsQuery(string $therapistId, string $date, string $timeSlotId, ?string $exceptAppointmentId = null)
    {
        $query = Appointment::query()
            ->where('therapist_id', $therapistId)
            ->whereDate('date', $date)
            ->where('time_slot_id', $timeSlotId)
            ->where(function ($q) {
                $q->whereIn('status', ['awaiting_verification', 'confirmed'])
                    ->orWhere(function ($q2) {
                        $q2->whereIn('status', ['pending', 'awaiting_payment'])
                            ->where(function ($q3) {
                                $q3->whereNull('slot_locked_until')
                                    ->orWhere('slot_locked_until', '>', now());
                            });
                    });
            });

        if ($exceptAppointmentId) {
            $query->where('id', '!=', $exceptAppointmentId);
        }

        return $query;
    }

    public function lockMinutes(): int
    {
        return config('booking.slot_lock_minutes', 15);
    }

    public function applySlotLock(Appointment $appointment): void
    {
        $appointment->update([
            'slot_locked_until' => now()->addMinutes($this->lockMinutes()),
        ]);
    }

    public function ensureTimeSlotRecord(string $therapistId, string $timeSlotId): void
    {
        TimeSlot::firstOrCreate(
            ['id' => $timeSlotId],
            [
                'therapist_id' => $therapistId,
                'time' => str_replace('-', ':', $timeSlotId),
                'available' => true,
            ]
        );
    }
}
