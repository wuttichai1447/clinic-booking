<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Service;
use App\Models\Therapist;
use App\Models\TimeSlot;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AppointmentReminderTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_sends_day_before_reminder(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-01 10:00:00'));

        $catalog = $this->seedCatalog();

        $appointment = Appointment::create([
            'clinic_id' => $catalog['clinic']->id,
            'service_id' => $catalog['service']->id,
            'therapist_id' => $catalog['therapist']->id,
            'date' => '2026-06-02',
            'time_slot_id' => '10-00',
            'customer_name' => 'Reminder Test',
            'customer_phone' => '0899999999',
            'customer_email' => 'reminder@example.com',
            'status' => 'confirmed',
            'amount' => 500,
            'subtotal' => 500,
            'discount_amount' => 0,
            'paid_at' => now(),
        ]);

        $this->artisan('booking:send-reminders')->assertSuccessful();

        $this->assertNotNull($appointment->fresh()->reminded_1d_at);
    }

    public function test_sends_two_hour_reminder(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-01 08:00:00'));

        $catalog = $this->seedCatalog();

        $appointment = Appointment::create([
            'clinic_id' => $catalog['clinic']->id,
            'service_id' => $catalog['service']->id,
            'therapist_id' => $catalog['therapist']->id,
            'date' => '2026-06-01',
            'time_slot_id' => '10-00',
            'customer_name' => 'Soon',
            'customer_phone' => '0877777777',
            'status' => 'confirmed',
            'amount' => 500,
            'subtotal' => 500,
            'discount_amount' => 0,
            'paid_at' => now(),
        ]);

        $this->artisan('booking:send-reminders')->assertSuccessful();

        $this->assertNotNull($appointment->fresh()->reminded_2h_at);
    }

    public function test_does_not_resend_day_reminder(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-01 10:00:00'));

        $catalog = $this->seedCatalog();

        Appointment::create([
            'clinic_id' => $catalog['clinic']->id,
            'service_id' => $catalog['service']->id,
            'therapist_id' => $catalog['therapist']->id,
            'date' => '2026-06-02',
            'time_slot_id' => '10-00',
            'customer_name' => 'Once',
            'customer_phone' => '0888888888',
            'status' => 'confirmed',
            'amount' => 500,
            'subtotal' => 500,
            'discount_amount' => 0,
            'reminded_1d_at' => now()->subHour(),
        ]);

        $this->artisan('booking:send-reminders')->assertSuccessful();

        $this->assertNull(
            Appointment::where('customer_phone', '0888888888')->value('reminded_2h_at')
        );
    }

    protected function seedCatalog(): array
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
            'id' => '10-00',
            'therapist_id' => $therapist->id,
            'time' => '10:00',
            'available' => true,
        ]);

        return compact('clinic', 'service', 'therapist');
    }
}
