<?php

use Illuminate\Support\Facades\Facade;

return [
    'name' => env('APP_NAME', 'Clinic Booking'),
    'env' => env('APP_ENV', 'production'),
    'debug' => (bool) env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'frontend_url' => env('FRONTEND_URL', 'http://localhost:3000'),
    'cron_key' => env('CRON_KEY'),
    'timezone' => 'Asia/Bangkok',
    'locale' => 'th',
    'fallback_locale' => 'en',
    'faker_locale' => 'th_TH',
    'cipher' => 'AES-256-CBC',
    'key' => env('APP_KEY'),
    'previous_keys' => [
        ...array_filter(
            explode(',', env('APP_PREVIOUS_KEYS', ''))
        ),
    ],
    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],
];
