<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\Service;
use App\Models\Therapist;
use App\Models\User;
use App\Services\SlotAvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminBookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_book_at_counter_as_confirmed(): void
    {
        $catalog = $this->seedCatalog();
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->post(route('admin.appointments.store'), [
            'clinic_id' => $catalog['clinic']->id,
            'service_id' => $catalog['service']->id,
            'therapist_id' => $catalog['therapist']->id,
            'date' => now()->addDays(5)->format('Y-m-d'),
            'time_slot_id' => '10-00',
            'customer_name' => 'ลูกค้า Walk-in',
            'customer_phone' => '0811111111',
            'payment_mode' => 'counter',
            'counter_method' => 'cash',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('appointments', [
            'customer_phone' => '0811111111',
            'status' => 'confirmed',
            'payment_method' => 'cash',
        ]);
        $this->assertDatabaseHas('payments', [
            'status' => 'succeeded',
            'provider' => 'counter',
        ]);
    }

    public function test_admin_can_book_pay_later(): void
    {
        $catalog = $this->seedCatalog();
        $admin = $this->adminUser();

        $this->actingAs($admin)->post(route('admin.appointments.store'), [
            'clinic_id' => $catalog['clinic']->id,
            'service_id' => $catalog['service']->id,
            'therapist_id' => $catalog['therapist']->id,
            'date' => now()->addDays(6)->format('Y-m-d'),
            'time_slot_id' => '11-00',
            'customer_name' => 'ลูกค้ารอจ่าย',
            'customer_phone' => '0822222222',
            'payment_mode' => 'later',
        ])->assertRedirect();

        $this->assertDatabaseHas('appointments', [
            'customer_phone' => '0822222222',
            'status' => 'awaiting_payment',
        ]);
    }

    public function test_shared_service_without_clinic_id_works_for_any_clinic(): void
    {
        $catalog = $this->seedCatalog();
        $shared = Service::create([
            'id' => (string) Str::uuid(),
            'name' => 'บริการร่วม',
            'duration' => 30,
            'price' => 500,
            'clinic_id' => null,
            'is_active' => true,
        ]);
        $admin = $this->adminUser();

        $this->actingAs($admin)->post(route('admin.appointments.store'), [
            'clinic_id' => $catalog['clinic']->id,
            'service_id' => $shared->id,
            'therapist_id' => $catalog['therapist']->id,
            'date' => now()->addDays(7)->format('Y-m-d'),
            'time_slot_id' => '10-00',
            'customer_name' => 'ลูกค้า',
            'customer_phone' => '0833333333',
            'payment_mode' => 'counter',
            'counter_method' => 'cash',
        ])->assertRedirect();

        $this->assertDatabaseHas('appointments', [
            'service_id' => $shared->id,
            'clinic_id' => $catalog['clinic']->id,
            'status' => 'confirmed',
        ]);
    }

    public function test_create_form_requires_admin(): void
    {
        $this->get(route('admin.appointments.create'))->assertRedirect(route('admin.login'));
    }

    protected function adminUser(): User
    {
        return User::create([
            'name' => 'Admin',
            'email' => 'admin-book@example.com',
            'password' => 'password',
            'role' => 'admin',
        ]);
    }

    /** @return array{clinic: Clinic, service: Service, therapist: Therapist} */
    protected function seedCatalog(): array
    {
        $clinic = Clinic::create([
            'id' => (string) Str::uuid(),
            'name' => 'คลินิก A',
            'address' => 'A',
            'phone' => '02',
            'is_active' => true,
        ]);
        $service = Service::create([
            'id' => (string) Str::uuid(),
            'name' => 'นวด',
            'duration' => 60,
            'price' => 800,
            'clinic_id' => $clinic->id,
            'is_active' => true,
        ]);
        $therapist = Therapist::create([
            'id' => (string) Str::uuid(),
            'name' => 'นักบำบัด 1',
            'clinic_id' => $clinic->id,
            'is_active' => true,
        ]);

        app(SlotAvailabilityService::class)->ensureTimeSlotRecord($therapist->id, '10-00');
        app(SlotAvailabilityService::class)->ensureTimeSlotRecord($therapist->id, '11-00');

        return compact('clinic', 'service', 'therapist');
    }
}
