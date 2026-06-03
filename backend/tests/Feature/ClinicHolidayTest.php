<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Models\ClinicHoliday;
use App\Models\Therapist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ClinicHolidayTest extends TestCase
{
    use RefreshDatabase;

    public function test_holiday_returns_empty_slots(): void
    {
        $clinic = Clinic::create([
            'id' => (string) Str::uuid(),
            'name' => 'Holiday Clinic',
            'address' => 'BKK',
            'phone' => '02',
            'is_active' => true,
        ]);
        $therapist = Therapist::create([
            'id' => (string) Str::uuid(),
            'name' => 'T',
            'clinic_id' => $clinic->id,
            'is_active' => true,
        ]);
        $date = now()->addDays(5)->format('Y-m-d');

        ClinicHoliday::create([
            'clinic_id' => $clinic->id,
            'date' => $date,
            'name' => 'Test holiday',
        ]);

        $response = $this->getJson('/api/v1/slots?'.http_build_query([
            'therapistId' => $therapist->id,
            'date' => $date,
            'clinicId' => $clinic->id,
        ]));

        $response->assertOk();
        $response->assertJson([]);
    }

    public function test_global_holiday_blocks_all_clinics(): void
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
            'name' => 'T',
            'clinic_id' => $clinic->id,
            'is_active' => true,
        ]);
        $date = now()->addDays(6)->format('Y-m-d');

        ClinicHoliday::create([
            'clinic_id' => null,
            'date' => $date,
            'name' => 'National holiday',
        ]);

        $response = $this->getJson('/api/v1/slots?'.http_build_query([
            'therapistId' => $therapist->id,
            'date' => $date,
            'clinicId' => $clinic->id,
        ]));

        $response->assertOk();
        $response->assertJson([]);
    }
}
