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

class SlotBookingRaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_second_booking_same_slot_is_rejected(): void
    {
        $catalog = $this->seedCatalog();
        $date = now()->addDays(5)->format('Y-m-d');

        $payload = [
            'clinicId' => $catalog['clinic']->id,
            'serviceId' => $catalog['service']->id,
            'therapistId' => $catalog['therapist']->id,
            'date' => $date,
            'timeSlotId' => '10-00',
            'customerName' => 'ลูกค้า A',
            'customerPhone' => '0811111111',
            'pdpaAccepted' => true,
        ];

        $this->postJson('/api/v1/appointments', $payload)->assertCreated();

        $payload['customerName'] = 'ลูกค้า B';
        $payload['customerPhone'] = '0822222222';

        $this->postJson('/api/v1/appointments', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['timeSlotId']);

        $this->assertEquals(1, Appointment::where('time_slot_id', '10-00')->whereDate('date', $date)->count());
    }

    /** @return array{clinic: Clinic, service: Service, therapist: Therapist} */
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
