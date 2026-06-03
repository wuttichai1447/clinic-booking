<?php

namespace App\Jobs;

use App\Services\PaymentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessStripeWebhookEventJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public array $backoff = [10, 30, 60, 120, 300];

    public function __construct(
        public string $eventType,
        public string $paymentIntentId,
    ) {}

    public function handle(PaymentService $payments): void
    {
        match ($this->eventType) {
            'payment_intent.succeeded' => $payments->handlePaymentIntentSucceeded($this->paymentIntentId),
            'payment_intent.payment_failed' => $payments->handlePaymentIntentFailed($this->paymentIntentId),
            default => null,
        };
    }
}
