<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\BookingNotification;
use App\Models\Clinic;
use App\Models\Service;
use App\Models\Therapist;
use App\Models\TimeSlot;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BookingNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_created_records_in_app_notification(): void
    {
        config(['booking.telegram.enabled' => false, 'booking.ntfy.enabled' => false]);

        $appointment = $this->makeAppointment();

        app(NotificationService::class)->bookingCreated($appointment);

        $this->assertDatabaseCount('booking_notifications', 1);

        $notification = BookingNotification::first();
        $this->assertSame('booking_created', $notification->event_type);
        $this->assertSame($appointment->id, $notification->appointment_id);
        $this->assertNull($notification->read_at);
        $this->assertStringContainsString('Notify Test', $notification->message);
        $this->assertStringContainsString('C', $notification->message);
    }

    public function test_admin_can_view_and_mark_notifications(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'password',
            'role' => 'admin',
        ]);
        $appointment = $this->makeAppointment();

        $notification = BookingNotification::record(
            'payment_confirmed',
            'ชำระเงินแล้ว',
            'ทดสอบ',
            $appointment,
        );

        $this->actingAs($admin)
            ->get(route('admin.booking-notifications.index'))
            ->assertOk()
            ->assertSee('ชำระเงินแล้ว');

        $this->actingAs($admin)
            ->post(route('admin.booking-notifications.read', $notification))
            ->assertRedirect();

        $this->assertNotNull($notification->fresh()->read_at);
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
            'status' => 'pending_payment',
            'amount' => 500,
            'subtotal' => 500,
            'discount_amount' => 0,
        ]);
    }
}
