<?php

return [
    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

    'payment' => [
        'bank_name' => env('PAYMENT_BANK_NAME', 'ธนาคารกสิกรไทย'),
        'bank_account_name' => env('PAYMENT_BANK_ACCOUNT_NAME', 'Clinic Booking Co., Ltd.'),
        'bank_account' => env('PAYMENT_BANK_ACCOUNT'),
        'promptpay_id' => env('PAYMENT_PROMPTPAY_ID'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', env('APP_URL').'/api/v1/auth/google/callback'),
    ],

    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('FACEBOOK_REDIRECT_URI', env('APP_URL').'/api/v1/auth/facebook/callback'),
    ],

    'partners' => [
        'enabled' => env('PARTNERS_API_ENABLED', false),
        'url' => env('PARTNERS_API_URL'),
        'key' => env('PARTNERS_API_KEY'),
    ],
];
