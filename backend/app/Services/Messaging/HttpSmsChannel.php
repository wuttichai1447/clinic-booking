<?php

namespace App\Services\Messaging;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HttpSmsChannel
{
    public function isConfigured(): bool
    {
        return config('booking.sms.enabled') && filled(config('booking.sms.url'));
    }

    public function send(string $phone, string $message): bool
    {
        if (! $this->isConfigured() || ! $phone) {
            return false;
        }

        try {
            $response = Http::withHeaders(array_filter([
                'Authorization' => config('booking.sms.api_key')
                    ? 'Bearer '.config('booking.sms.api_key')
                    : null,
            ]))
                ->timeout(10)
                ->post(config('booking.sms.url'), [
                    'phone' => $phone,
                    'message' => $message,
                ]);

            if (! $response->successful()) {
                Log::warning('SMS API failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::warning('SMS API exception', ['error' => $e->getMessage()]);

            return false;
        }
    }
}
