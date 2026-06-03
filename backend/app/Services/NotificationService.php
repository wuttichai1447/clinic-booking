<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\BookingNotification;
use App\Models\Payment;
use App\Services\Messaging\HttpSmsChannel;
use App\Services\Messaging\NtfyChannel;
use App\Services\Messaging\TelegramChannel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    public function __construct(
        protected TelegramChannel $telegram,
        protected NtfyChannel $ntfy,
        protected HttpSmsChannel $smsGateway,
        protected BookingNotificationFormatter $formatter,
    ) {}

    public function bookingCreated(Appointment $appointment): void
    {
        $appointment = $this->formatter->loadRelations($appointment);

        $this->recordInApp(
            'booking_created',
            'การจองใหม่ — รอชำระเงิน',
            $appointment,
        );

        $this->mailToCustomer($appointment, 'การจองสำเร็จ — รอชำระเงิน', 'emails.booking-created');

        $msg = "จองสำเร็จ #{$appointment->id} ยอด ฿".number_format($appointment->amount).' รอชำระเงิน';
        $this->pushToCustomer($appointment->customer_phone, $msg);

        $this->alertAdmin(
            'จองใหม่ — รอชำระเงิน',
            $this->formatter->adminPushMessage('📅', 'จองใหม่ — รอชำระเงิน', $appointment),
        );
    }

    public function paymentConfirmed(Appointment $appointment): void
    {
        $appointment = $this->formatter->loadRelations($appointment);

        $this->recordInApp(
            'payment_confirmed',
            'ชำระเงินแล้ว — ยืนยันการจอง',
            $appointment,
        );

        $this->mailToCustomer($appointment, 'ชำระเงินสำเร็จ — ยืนยันการจองแล้ว', 'emails.payment-confirmed');

        $msg = "ชำระเงินสำเร็จ การจอง #{$appointment->id} ยืนยันแล้ว";
        $this->pushToCustomer($appointment->customer_phone, $msg);

        $this->alertAdmin(
            'ชำระเงินแล้ว — ยืนยันการจอง',
            $this->formatter->adminPushMessage('✅', 'ชำระเงินแล้ว — ยืนยันการจอง', $appointment),
        );
    }

    public function awaitingVerification(Appointment $appointment, Payment $payment): void
    {
        $appointment = $this->formatter->loadRelations($appointment);

        $this->recordInApp(
            'awaiting_verification',
            'มีการโอนรอตรวจสอบ',
            $appointment,
        );

        $this->mailToCustomer($appointment, 'รับหลักฐานการโอนแล้ว — รอแอดมินยืนยัน', 'emails.awaiting-verification');

        $this->alertAdmin(
            'มีการโอนรอตรวจสอบ',
            $this->formatter->summaryLine($appointment)."\n#{$appointment->id}",
        );

        $msg = "รับหลักฐานโอนแล้ว รอแอดมินยืนยัน #{$appointment->id}";
        $this->pushToCustomer($appointment->customer_phone, $msg);
    }

    public function appointmentCancelled(Appointment $appointment): void
    {
        $appointment = $this->formatter->loadRelations($appointment);

        $refund = $appointment->refund_amount
            ? ' คืนเงิน ฿'.number_format($appointment->refund_amount)
            : '';

        $this->recordInApp(
            'appointment_cancelled',
            'การจองถูกยกเลิก',
            $appointment,
            $refund,
        );

        $this->mailToCustomer($appointment, 'การจองถูกยกเลิก', 'emails.appointment-cancelled');

        $msg = "ยกเลิกการจอง #{$appointment->id}{$refund}";
        $this->pushToCustomer($appointment->customer_phone, $msg);

        $this->alertAdmin(
            'ยกเลิกการจอง',
            $this->formatter->adminPushMessage('❌', 'ยกเลิกการจอง', $appointment).$refund,
        );
    }

    public function appointmentRescheduled(Appointment $appointment): void
    {
        $appointment = $this->formatter->loadRelations($appointment);

        $this->recordInApp(
            'appointment_rescheduled',
            'เลื่อนนัดสำเร็จ',
            $appointment,
        );

        $this->mailToCustomer($appointment, 'เลื่อนนัดสำเร็จ', 'emails.appointment-rescheduled');

        $slot = str_replace('-', ':', $appointment->time_slot_id);
        $msg = "เลื่อนนัด #{$appointment->id} เป็น {$appointment->date->format('d/m/Y')} {$slot}";
        $this->pushToCustomer($appointment->customer_phone, $msg);

        $this->alertAdmin(
            'เลื่อนนัด',
            $this->formatter->adminPushMessage('🔄', 'เลื่อนนัด', $appointment),
        );
    }

    public function appointmentReminder(Appointment $appointment, string $type): void
    {
        $appointment = $this->formatter->loadRelations($appointment);

        $labels = [
            '1d' => ['title' => 'แจ้งเตือนนัด — พรุ่งนี้', 'email' => 'แจ้งเตือนนัดพรุ่งนี้'],
            '2h' => ['title' => 'แจ้งเตือนนัด — อีก 2 ชั่วโมง', 'email' => 'แจ้งเตือนนัดอีก 2 ชั่วโมง'],
        ];

        $meta = $labels[$type] ?? $labels['2h'];
        $slot = str_replace('-', ':', $appointment->time_slot_id);
        $body = "คุณ{$appointment->customer_name} มีนัด {$appointment->date->format('d/m/Y')} เวลา {$slot}\n"
            .$this->formatter->summaryLine($appointment)
            ."\n#{$appointment->id}";

        $this->mailToCustomer($appointment, $meta['email'], 'emails.appointment-reminder');

        $sms = "{$meta['title']}: {$appointment->date->format('d/m/Y')} {$slot} — {$appointment->clinic?->name}";
        $this->pushToCustomer($appointment->customer_phone, $sms);
    }

    protected function recordInApp(
        string $eventType,
        string $title,
        Appointment $appointment,
        string $extra = '',
    ): void {
        $message = $this->formatter->summaryLine($appointment);
        if ($extra !== '') {
            $message .= "\n".$extra;
        }

        BookingNotification::record($eventType, $title, $message, $appointment);
    }

    protected function mailToCustomer(Appointment $appointment, string $subject, string $view): void
    {
        if (! filled($appointment->customer_email)) {
            return;
        }

        try {
            Mail::send($view, ['appointment' => $appointment], function ($message) use ($appointment, $subject) {
                $message->to($appointment->customer_email, $appointment->customer_name)
                    ->subject($subject);
            });
        } catch (\Throwable $e) {
            Log::warning('Mail failed', ['view' => $view, 'error' => $e->getMessage()]);
        }
    }

    protected function alertAdmin(string $subject, string $body): void
    {
        $this->mailToAdmin($subject, $body);
        $this->pushToAdmin($body);
    }

    protected function mailToAdmin(string $subject, string $body): void
    {
        $email = config('booking.notifications.admin_email');
        if (! filled($email)) {
            return;
        }

        try {
            Mail::raw($body, fn ($m) => $m->to($email)->subject('[จองคลินิก] '.$subject));
        } catch (\Throwable $e) {
            Log::warning('Admin mail failed', ['error' => $e->getMessage()]);
        }
    }

    protected function pushToCustomer(?string $phone, string $text): void
    {
        if (! $phone) {
            return;
        }

        if ($this->smsGateway->isConfigured() && $this->smsGateway->send($phone, $text)) {
            return;
        }

        Log::channel('single')->info('[SMS] '.$phone.' — '.$text);
    }

    protected function pushToAdmin(string $message): void
    {
        if ($this->telegram->isConfigured()) {
            $this->telegram->send($message);

            return;
        }

        if ($this->ntfy->isConfigured()) {
            $this->ntfy->send($message, 'ระบบจองคลินิก');

            return;
        }

        Log::channel('single')->info('[Admin notify] '.$message);
    }
}
