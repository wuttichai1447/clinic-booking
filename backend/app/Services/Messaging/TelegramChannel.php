<?php

namespace App\Services\Messaging;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * แจ้งเตือนแอดมินผ่าน Telegram Bot (ทดแทน LINE Notify ที่ยุตให้บริการแล้ว).
 *
 * @see https://core.telegram.org/bots/api#sendmessage
 */
class TelegramChannel
{
    public function isConfigured(): bool
    {
        return config('booking.telegram.enabled')
            && filled(config('booking.telegram.bot_token'))
            && filled(config('booking.telegram.chat_id'));
    }

    public function send(string $message): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }

        $token = config('booking.telegram.bot_token');
        $chatId = config('booking.telegram.chat_id');

        try {
            $response = Http::timeout(10)
                ->post("https://api.telegram.org/bot{$token}/sendMessage", [
                    'chat_id' => $chatId,
                    'text' => $message,
                    'disable_web_page_preview' => true,
                ]);

            if (! $response->successful() || ! ($response->json('ok') ?? false)) {
                Log::warning('Telegram send failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::warning('Telegram exception', ['error' => $e->getMessage()]);

            return false;
        }
    }
}
