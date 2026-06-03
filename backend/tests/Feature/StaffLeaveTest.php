<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\StaffLeave;
use App\Models\Therapist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class StaffLeaveTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_leave_blocks_slots(): void
    {
        $clinic = Clinic::create([
            'id' => (string) Str::uuid(),
            'name' => 'C',
            'address' => 'A',
            'phone' => '02',
            'is_active' => true,
        ]);
        $therapist = Therapist::create([
            'id' => (string) Str::uuid(),
            'name' => 'Staff A',
            'clinic_id' => $clinic->id,
            'is_active' => true,
        ]);
        $date = now()->addDays(10)->format('Y-m-d');

        StaffLeave::create([
            'therapist_id' => $therapist->id,
            'start_date' => $date,
            'end_date' => $date,
            'leave_type' => 'sick',
        ]);

        $response = $this->getJson('/api/v1/slots?'.http_build_query([
            'therapistId' => $therapist->id,
            'date' => $date,
            'clinicId' => $clinic->id,
        ]));

        $response->assertOk();
        $response->assertJson([]);
    }

    public function test_booking_rejected_when_therapist_on_leave(): void
    {
        $clinic = Clinic::create([
            'id' => (string) Str::uuid(),
            'name' => 'C',
            'address' => 'A',
            'phone' => '02',
            'is_active' => true,
        ]);
        $therapist = Therapist::create([
            'id' => (string) Str::uuid(),
            'name' => 'Staff B',
            'clinic_id' => $clinic->id,
            'is_active' => true,
        ]);
        $service = \App\Models\Service::create([
            'id' => (string) Str::uuid(),
            'name' => 'S',
            'duration' => 60,
            'price' => 500,
            'clinic_id' => $clinic->id,
            'is_active' => true,
        ]);
        $date = now()->addDays(11)->format('Y-m-d');

        StaffLeave::create([
            'therapist_id' => $therapist->id,
            'start_date' => $date,
            'end_date' => $date,
            'leave_type' => 'annual',
        ]);

        $response = $this->postJson('/api/v1/appointments', [
            'clinicId' => $clinic->id,
            'serviceId' => $service->id,
            'therapistId' => $therapist->id,
            'date' => $date,
            'timeSlotId' => '09-00',
            'customerName' => 'Guest',
            'customerPhone' => '0812345678',
            'pdpaAccepted' => true,
        ]);

        $response->assertStatus(422);
    }
}
