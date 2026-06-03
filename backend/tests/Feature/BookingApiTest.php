<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\Service;
use App\Models\Therapist;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BookingApiTest extends TestCase
{
    use RefreshDatabase;

    protected function seedMinimalCatalog(): array
    {
        $clinic = Clinic::create([
            'id' => (string) Str::uuid(),
            'name' => 'Test Clinic',
            'address' => 'Bangkok',
            'phone' => '021234567',
            'is_active' => true,
        ]);
        $service = Service::create([
            'id' => (string) Str::uuid(),
            'name' => 'Test Service',
            'duration' => 60,
            'price' => 500,
            'clinic_id' => $clinic->id,
            'is_active' => true,
        ]);
        $therapist = Therapist::create([
            'id' => (string) Str::uuid(),
            'name' => 'Test Therapist',
            'clinic_id' => $clinic->id,
            'is_active' => true,
        ]);

        return compact('clinic', 'service', 'therapist');
    }

    public function test_create_appointment_requires_pdpa(): void
    {
        $catalog = $this->seedMinimalCatalog();

        $response = $this->postJson('/api/v1/appointments', [
            'clinicId' => $catalog['clinic']->id,
            'serviceId' => $catalog['service']->id,
            'therapistId' => $catalog['therapist']->id,
            'date' => now()->addDay()->format('Y-m-d'),
            'timeSlotId' => '09-00',
            'customerName' => 'Test User',
            'customerPhone' => '0812345678',
        ]);

        $response->assertStatus(422);
    }

    public function test_customer_booking_links_user_id(): void
    {
        $catalog = $this->seedMinimalCatalog();
        $user = User::create([
            'name' => 'Customer Test',
            'email' => 'customer-test@example.com',
            'phone' => '0899999999',
            'password' => 'password',
            'role' => 'customer',
            'pdpa_accepted_at' => now(),
        ]);
        $token = $user->createToken('customer')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/appointments', [
                'clinicId' => $catalog['clinic']->id,
                'serviceId' => $catalog['service']->id,
                'therapistId' => $catalog['therapist']->id,
                'date' => now()->addDays(2)->format('Y-m-d'),
                'timeSlotId' => '10-00',
                'customerName' => $user->name,
                'customerPhone' => $user->phone,
                'pdpaAccepted' => true,
            ]);

        $response->assertCreated();
        $this->assertDatabaseHas('appointments', [
            'user_id' => $user->id,
            'status' => 'awaiting_payment',
        ]);
    }
}
