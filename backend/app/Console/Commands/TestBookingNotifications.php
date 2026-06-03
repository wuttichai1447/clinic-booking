<?php

namespace App\Console\Commands;

use App\Services\Messaging\HttpSmsChannel;
use App\Services\Messaging\NtfyChannel;
use App\Services\Messaging\TelegramChannel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestBookingNotifications extends Command
{
    protected $signature = 'booking:notify-test
                            {--phone= : ทดสอบ SMS ไปเบอร์นี้}
                            {--email= : ทดสอบอีเมลไปที่นี้}';

    protected $description = 'ทดสอบช่องทางแจ้งเตือน (แสดงสถานะ + ส่งข้อความทดสอบ)';

    public function handle(
        TelegramChannel $telegram,
        NtfyChannel $ntfy,
        HttpSmsChannel $sms,
    ): int {
        $mailer = config('mail.default');
        $adminEmail = config('booking.notifications.admin_email');

        $this->info('=== สถานะการตั้งค่าแจ้งเตือน ===');
        $this->table(
            ['ช่องทาง', 'สถานะ', 'หมายเหตุ'],
            [
                ['อีเมล (MAIL_MAILER)', $mailer, $mailer === 'log' ? 'เขียน log เท่านั้น — ตั้ง smtp ใน .env' : 'พร้อมส่ง'],
                ['อีเมลแอดมิน', filled($adminEmail) ? $adminEmail : '—', filled($adminEmail) ? 'OK' : 'ตั้ง ADMIN_NOTIFY_EMAIL'],
                ['Telegram', $telegram->isConfigured() ? 'เปิด' : 'ปิด', 'TELEGRAM_ENABLED + token + chat_id'],
                ['ntfy (มือถือ)', $ntfy->isConfigured() ? 'เปิด' : 'ปิด', 'NTFY_ENABLED + NTFY_TOPIC + แอป ntfy'],
                ['SMS ลูกค้า', $sms->isConfigured() ? 'เปิด' : 'ปิด', 'SMS_ENABLED + SMS_API_URL'],
            ]
        );

        $message = 'ทดสอบแจ้งเตือน — ระบบจองคลินิก '.now()->format('d/m/Y H:i');

        if ($telegram->isConfigured()) {
            $ok = $telegram->send($message);
            $this->line($ok ? '<info>✓ Telegram ส่งแล้ว</info>' : '<error>✗ Telegram ส่งไม่สำเร็จ (ดู log)</error>');
        }

        if ($ntfy->isConfigured()) {
            $ok = $ntfy->send($message, 'ทดสอบแจ้งเตือน');
            $this->line($ok ? '<info>✓ ntfy ส่งแล้ว (เช็คแอปมือถือ)</info>' : '<error>✗ ntfy ส่งไม่สำเร็จ</error>');
        }

        if (! $telegram->isConfigured() && ! $ntfy->isConfigured()) {
            $this->warn('ยังไม่ได้ตั้ง Telegram หรือ ntfy — แอดมินจะได้แค่ log');
        }

        $testEmail = $this->option('email') ?: $adminEmail;
        if ($testEmail && $mailer !== 'log') {
            try {
                Mail::raw($message, fn ($m) => $m->to($testEmail)->subject('ทดสอบแจ้งเตือน — Clinic Booking'));
                $this->line("<info>✓ อีเมลส่งไป {$testEmail}</info>");
            } catch (\Throwable $e) {
                $this->error('✗ อีเมล: '.$e->getMessage());
            }
        } elseif ($testEmail && $mailer === 'log') {
            Mail::raw($message, fn ($m) => $m->to($testEmail)->subject('ทดสอบแจ้งเตือน'));
            $this->line('<comment>อีเมลบันทึกใน storage/logs/laravel.log (MAIL_MAILER=log)</comment>');
        }

        $phone = $this->option('phone');
        if ($phone && $sms->isConfigured()) {
            $ok = $sms->send($phone, $message);
            $this->line($ok ? "<info>✓ SMS ส่งไป {$phone}</info>" : '<error>✗ SMS ส่งไม่สำเร็จ</error>');
        }

        return self::SUCCESS;
    }
}
