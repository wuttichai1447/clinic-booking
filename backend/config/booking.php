<?php

return [
    'slot_lock_minutes' => (int) env('BOOKING_SLOT_LOCK_MINUTES', 15),

    'reminders' => [
        'enabled' => (bool) env('BOOKING_REMINDERS_ENABLED', true),
        'day_before_hours' => (int) env('BOOKING_REMINDER_DAY_HOURS', 24),
        'short_before_hours' => (int) env('BOOKING_REMINDER_SHORT_HOURS', 2),
        'window_minutes' => (int) env('BOOKING_REMINDER_WINDOW_MINUTES', 30),
    ],

    'refund' => [
        'full_refund_hours_before' => (int) env('REFUND_FULL_HOURS', 24),
        'partial_refund_percent' => (int) env('REFUND_PARTIAL_PERCENT', 50),
        'partial_refund_hours_before' => (int) env('REFUND_PARTIAL_HOURS', 12),
        'no_refund_within_hours' => (int) env('REFUND_NONE_HOURS', 2),
    ],

    'notifications' => [
        'admin_email' => env('ADMIN_NOTIFY_EMAIL', env('ADMIN_EMAIL')),
    ],

    'ntfy' => [
        'enabled' => (bool) env('NTFY_ENABLED', false),
        'topic' => env('NTFY_TOPIC'),
        'server' => env('NTFY_SERVER', 'https://ntfy.sh'),
    ],

    'telegram' => [
        'enabled' => (bool) env('TELEGRAM_ENABLED', false),
        'bot_token' => env('TELEGRAM_BOT_TOKEN'),
        'chat_id' => env('TELEGRAM_CHAT_ID'),
    ],

    'sms' => [
        'enabled' => (bool) env('SMS_ENABLED', false),
        'url' => env('SMS_API_URL'),
        'api_key' => env('SMS_API_KEY'),
    ],
];
