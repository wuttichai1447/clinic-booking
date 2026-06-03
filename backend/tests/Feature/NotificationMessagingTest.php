<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Service;
use App\Models\Therapist;
use App\Models\TimeSlot;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class NotificationMessagingTest extends TestCase
{
    use RefreshDatabase;

    public function test_telegram_sent_when_configured(): void
    {
        config([
            'booking.telegram.enabled' => true,
            'booking.telegram.bot_token' => '123456:ABC-DEF',
            'booking.telegram.chat_id' => '-1001234567890',
            'booking.sms.enabled' => false,
        ]);

        Http::fake([
            'api.telegram.org/*' => Http::response(['ok' => true, 'result' => []], 200),
        ]);

        $appointment = $this->makeAppointment();

        app(NotificationService::class)->paymentConfirmed($appointment);

        Http::assertSent(function ($request) {
            $text = $request->data()['text'] ?? '';

            return str_contains($request->url(), 'api.telegram.org')
                && str_contains($text, 'ชำระเงินแล้ว');
        });
    }

    protected function makeAppointment(): Appointment
    {
        $clinic = Clinic::create([
            'id' => (string) Str::uuid(),
            'name' => 'C',
            'address' => 'A',
            'phone' => '02',
            'is_active' => true,
        ]);
        $service = Service::create([
            'id' => (string) Str::uuid(),
            'name' => 'S',
            'duration' => 30,
            'price' => 500,
            'clinic_id' => $clinic->id,
            'is_active' => true,
        ]);
        $therapist = Therapist::create([
            'id' => (string) Str::uuid(),
            'name' => 'T',
            'clinic_id' => $clinic->id,
            'is_active' => true,
        ]);

        TimeSlot::create([
            'id' => '09-00',
            'therapist_id' => $therapist->id,
            'time' => '09:00',
            'available' => true,
        ]);

        return Appointment::create([
            'clinic_id' => $clinic->id,
            'service_id' => $service->id,
            'therapist_id' => $therapist->id,
            'date' => now()->addDay(),
            'time_slot_id' => '09-00',
            'customer_name' => 'Notify Test',
            'customer_phone' => '0899999888',
            'status' => 'confirmed',
            'amount' => 500,
            'subtotal' => 500,
            'discount_amount' => 0,
        ]);
    }
}
