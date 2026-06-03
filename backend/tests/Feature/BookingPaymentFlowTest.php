<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\ClinicHoliday;
use App\Models\Service;
use App\Models\Therapist;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BookingPaymentFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function seedCatalog(): array
    {
        $clinic = Clinic::create([
            'id' => (string) Str::uuid(),
            'name' => 'Flow Clinic',
            'address' => 'Bangkok',
            'phone' => '021234567',
            'is_active' => true,
        ]);
        $service = Service::create([
            'id' => (string) Str::uuid(),
            'name' => 'Massage',
            'duration' => 60,
            'price' => 800,
            'clinic_id' => $clinic->id,
            'is_active' => true,
        ]);
        $therapist = Therapist::create([
            'id' => (string) Str::uuid(),
            'name' => 'Dr. Test',
            'clinic_id' => $clinic->id,
            'is_active' => true,
        ]);

        return compact('clinic', 'service', 'therapist');
    }

    public function test_full_booking_and_dev_payment_flow(): void
    {
        $catalog = $this->seedCatalog();
        $date = now()->addDays(4)->format('Y-m-d');

        $create = $this->postJson('/api/v1/appointments', [
            'clinicId' => $catalog['clinic']->id,
            'serviceId' => $catalog['service']->id,
            'therapistId' => $catalog['therapist']->id,
            'date' => $date,
            'timeSlotId' => '09-00',
            'customerName' => 'Guest User',
            'customerPhone' => '0812345678',
            'pdpaAccepted' => true,
        ]);

        $create->assertCreated();
        $appointmentId = $create->json('id');
        $this->assertNotEmpty($appointmentId);

        $pay = $this->postJson("/api/v1/appointments/{$appointmentId}/payments/dev-complete", [
            'method' => 'credit_card',
        ]);

        $pay->assertOk();
        $pay->assertJsonPath('status', 'confirmed');

        $this->assertDatabaseHas('appointments', [
            'id' => $appointmentId,
            'status' => 'confirmed',
        ]);
    }

    public function test_cannot_book_on_clinic_holiday(): void
    {
        $catalog = $this->seedCatalog();
        $date = now()->addDays(7)->format('Y-m-d');

        ClinicHoliday::create([
            'clinic_id' => $catalog['clinic']->id,
            'date' => $date,
            'name' => 'Closed',
        ]);

        $response = $this->postJson('/api/v1/appointments', [
            'clinicId' => $catalog['clinic']->id,
            'serviceId' => $catalog['service']->id,
            'therapistId' => $catalog['therapist']->id,
            'date' => $date,
            'timeSlotId' => '10-00',
            'customerName' => 'Guest',
            'customerPhone' => '0811111111',
            'pdpaAccepted' => true,
        ]);

        $response->assertStatus(422);
    }

    public function test_reschedule_uses_slots_api(): void
    {
        $catalog = $this->seedCatalog();
        $user = User::create([
            'name' => 'Reschedule User',
            'email' => 'reschedule@example.com',
            'phone' => '0822222222',
            'password' => 'password',
            'role' => 'customer',
            'pdpa_accepted_at' => now(),
        ]);
        $token = $user->createToken('test')->plainTextToken;

        $date1 = now()->addDays(8)->format('Y-m-d');
        $date2 = now()->addDays(9)->format('Y-m-d');

        $create = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/appointments', [
                'clinicId' => $catalog['clinic']->id,
                'serviceId' => $catalog['service']->id,
                'therapistId' => $catalog['therapist']->id,
                'date' => $date1,
                'timeSlotId' => '11-00',
                'customerName' => $user->name,
                'customerPhone' => $user->phone,
                'pdpaAccepted' => true,
            ]);

        $create->assertCreated();
        $id = $create->json('id');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson("/api/v1/appointments/{$id}/payments/dev-complete", ['method' => 'credit_card'])
            ->assertOk();

        $reschedule = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson("/api/v1/appointments/{$id}/reschedule", [
                'date' => $date2,
                'timeSlotId' => '13-00',
            ]);

        $reschedule->assertOk();
        $reschedule->assertJsonPath('date', $date2);
        $reschedule->assertJsonPath('timeSlotId', '13-00');
    }
}
