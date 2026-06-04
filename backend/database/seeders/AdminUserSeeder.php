<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = trim((string) (env('ADMIN_EMAIL') ?: 'admin@booking.local'));
        $password = trim((string) (env('ADMIN_PASSWORD') ?: 'password'));

        $user = User::firstOrNew(['email' => $email]);
        $user->name = 'Admin';
        $user->role = 'admin';
        $user->phone = null;
        $user->password = $password;
        $user->save();
    }
}
