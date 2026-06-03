<?php

namespace App\Services;

use App\Models\Appointment;
use Carbon\Carbon;

class RefundService
{
    /**
     * @return array{eligible: bool, amount: int, policy: string}
     */
    public function evaluate(Appointment $appointment): array
    {
        $cfg = config('booking.refund');
        $amount = $appointment->amount;
        $startsAt = Carbon::parse($appointment->date->format('Y-m-d').' '.str_replace('-', ':', $appointment->time_slot_id));
        $hoursUntil = now()->diffInHours($startsAt, false);

        if ($hoursUntil < $cfg['no_refund_within_hours']) {
            return ['eligible' => false, 'amount' => 0, 'policy' => 'no_refund_late'];
        }

        if ($hoursUntil >= $cfg['full_refund_hours_before']) {
            return ['eligible' => true, 'amount' => $amount, 'policy' => 'full_refund'];
        }

        if ($hoursUntil >= $cfg['partial_refund_hours_before']) {
            $partial = (int) round($amount * ($cfg['partial_refund_percent'] / 100));

            return ['eligible' => true, 'amount' => $partial, 'policy' => 'partial_refund'];
        }

        return ['eligible' => false, 'amount' => 0, 'policy' => 'no_refund'];
    }
}
