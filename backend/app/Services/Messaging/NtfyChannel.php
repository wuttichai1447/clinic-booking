<?php

namespace App\Services\Messaging;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Push แจ้งแอดมินผ่าน ntfy (ติดตั้งแอป ntfy แล้ว subscribe ตาม topic).
 *
 * @see https://ntfy.sh/
 */
class NtfyChannel
{
    public function isConfigured(): bool
    {
        return config('booking.ntfy.enabled') && filled(config('booking.ntfy.topic'));
    }

    public function send(string $message, ?string $title = null): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }

        $server = rtrim(config('booking.ntfy.server', 'https://ntfy.sh'), '/');
        $topic = config('booking.ntfy.topic');

        try {
            $response = Http::timeout(10)
                ->withHeaders(array_filter([
                    'Title' => $title ?? config('app.name'),
                    'Priority' => 'default',
                    'Tags' => 'bell',
                ]))
                ->withBody($message, 'text/plain; charset=utf-8')
                ->post("{$server}/{$topic}");

            if (! $response->successful()) {
                Log::warning('ntfy send failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::warning('ntfy exception', ['error' => $e->getMessage()]);

            return false;
        }
    }
}
