<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Service;
use App\Models\Therapist;
use App\Models\TimeSlot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PaymentDevTest extends TestCase
{
    use RefreshDatabase;

    public function test_dev_complete_confirms_appointment(): void
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
            'id' => '11-00',
            'therapist_id' => $therapist->id,
            'time' => '11:00',
            'available' => true,
        ]);

        $appointment = Appointment::create([
            'clinic_id' => $clinic->id,
            'service_id' => $service->id,
            'therapist_id' => $therapist->id,
            'date' => now()->addDays(3),
            'time_slot_id' => '11-00',
            'customer_name' => 'Pay Test',
            'customer_phone' => '0811111111',
            'status' => 'awaiting_payment',
            'amount' => 500,
            'subtotal' => 500,
            'discount_amount' => 0,
        ]);

        $response = $this->postJson("/api/v1/appointments/{$appointment->id}/payments/dev-complete", [
            'method' => 'credit_card',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'confirmed',
        ]);
    }
}
