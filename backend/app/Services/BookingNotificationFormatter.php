<?php

namespace App\Services;

use App\Models\Appointment;

class BookingNotificationFormatter
{
    public function loadRelations(Appointment $appointment): Appointment
    {
        return $appointment->loadMissing(['clinic', 'service', 'therapist']);
    }

    public function summaryLine(Appointment $appointment): string
    {
        $appointment = $this->loadRelations($appointment);
        $slot = str_replace('-', ':', $appointment->time_slot_id);
        $clinic = $appointment->clinic?->name ?? '—';
        $service = $appointment->service?->name ?? '—';
        $therapist = $appointment->therapist?->name ?? '—';

        return "{$appointment->customer_name} · {$clinic}\n"
            ."{$service} · {$therapist}\n"
            ."{$appointment->date->format('d/m/Y')} {$slot} · ฿".number_format($appointment->amount);
    }

    public function adminPushMessage(string $emoji, string $headline, Appointment $appointment): string
    {
        return "{$emoji} {$headline}\n"
            .$this->summaryLine($appointment)
            ."\n#{$appointment->id}";
    }
}
