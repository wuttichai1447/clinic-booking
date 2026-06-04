<?php

namespace Database\Seeders;

use App\Models\Clinic;
use App\Models\Promotion;
use App\Models\Service;
use App\Models\Therapist;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    private function mockJson(string $file): array
    {
        $paths = [
            database_path('seeders/data/'.$file),
            base_path('../server/data/mock/'.$file),
        ];

        foreach ($paths as $path) {
            if (is_readable($path)) {
                return json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
            }
        }

        throw new \RuntimeException("Mock data file not found: {$file}");
    }

    public function run(): void
    {
        $clinics = $this->mockJson('clinics.json');
        foreach ($clinics as $c) {
            Clinic::updateOrCreate(['id' => $c['id']], [
                'name' => $c['name'],
                'address' => $c['address'],
                'phone' => $c['phone'],
                'image' => $c['image'] ?? null,
                'is_active' => true,
            ]);
        }

        $services = $this->mockJson('services.json');
        foreach ($services as $s) {
            Service::updateOrCreate(['id' => $s['id']], [
                'name' => $s['name'],
                'duration' => $s['duration'],
                'price' => $s['price'],
                'clinic_id' => $s['clinicId'] ?? null,
                'image' => $s['image'] ?? null,
                'is_active' => true,
            ]);
        }

        $therapists = $this->mockJson('therapists.json');
        foreach ($therapists as $t) {
            Therapist::updateOrCreate(['id' => $t['id']], [
                'name' => $t['name'],
                'specialty' => $t['specialty'] ?? null,
                'clinic_id' => $t['clinicId'] ?? null,
                'image' => $t['image'] ?? null,
                'is_active' => true,
            ]);
        }

        User::updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@booking.local')],
            [
                'name' => 'Admin',
                'password' => Hash::make(env('ADMIN_PASSWORD', 'password')),
                'role' => 'admin',
                'phone' => null,
            ]
        );

        Promotion::updateOrCreate(
            ['code' => 'WELCOME10'],
            [
                'title' => 'ลูกค้าใหม่ ลด 10%',
                'type' => 'percent',
                'value' => 10,
                'min_amount' => 500,
                'max_uses' => 100,
                'is_active' => true,
            ]
        );
    }
}
