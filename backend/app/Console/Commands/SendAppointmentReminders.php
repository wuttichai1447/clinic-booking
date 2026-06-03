<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class SendAppointmentReminders extends Command
{
    protected $signature = 'booking:send-reminders';

    protected $description = 'ส่งแจ้งเตือนลูกค้าก่อนนัด (1 วัน / 2 ชม.)';

    public function handle(NotificationService $notify): int
    {
        if (! config('booking.reminders.enabled')) {
            $this->warn('ปิดการแจ้งเตือนนัด (BOOKING_REMINDERS_ENABLED=false)');

            return self::SUCCESS;
        }

        $dayHours = (int) config('booking.reminders.day_before_hours', 24);
        $shortHours = (int) config('booking.reminders.short_before_hours', 2);
        $window = (int) config('booking.reminders.window_minutes', 30);

        $sentDay = $this->sendForWindow($notify, '1d', 'reminded_1d_at', $dayHours, $window);
        $sentShort = $this->sendForWindow($notify, '2h', 'reminded_2h_at', $shortHours, $window);

        $this->info("ส่งแจ้งเตือนแล้ว — ล่วงหน้า {$dayHours} ชม.: {$sentDay} รายการ, ล่วงหน้า {$shortHours} ชม.: {$sentShort} รายการ");

        return self::SUCCESS;
    }

    protected function sendForWindow(
        NotificationService $notify,
        string $type,
        string $sentColumn,
        int $hoursBefore,
        int $windowMinutes,
    ): int {
        $now = now();
        $from = $now->copy()->addHours($hoursBefore)->subMinutes($windowMinutes);
        $to = $now->copy()->addHours($hoursBefore)->addMinutes($windowMinutes);

        $appointments = Appointment::query()
            ->where('status', 'confirmed')
            ->whereNull($sentColumn)
            ->whereDate('date', '>=', $now->toDateString())
            ->with(['clinic', 'service', 'therapist'])
            ->get()
            ->filter(function (Appointment $appointment) use ($from, $to) {
                $starts = $appointment->startsAt();

                return $starts->between($from, $to);
            });

        $count = 0;
        foreach ($appointments as $appointment) {
            $notify->appointmentReminder($appointment, $type);
            $appointment->update([$sentColumn => now()]);
            $count++;
        }

        return $count;
    }
}
