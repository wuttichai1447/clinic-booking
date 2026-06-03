<?php

namespace App\Services;

use App\Jobs\SubmitAppointmentToPartnersJob;
use App\Models\Appointment;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class PaymentService
{
    public function __construct(protected NotificationService $notify) {}

    /** Stripe THB ขั้นต่ำ ฿10 (หน่วยสตางค์ = 1000) */
    public const STRIPE_THB_MIN_AMOUNT = 10;
    public function config(): array
    {
        $stripeEnabled = $this->useStripe();

        return [
            'devMode' => $this->devModeEnabled(),
            'stripeEnabled' => $stripeEnabled,
            'publishableKey' => $stripeEnabled ? config('services.stripe.key') : null,
            'methods' => [
                'credit_card' => $stripeEnabled || $this->devModeEnabled(),
                'transfer' => $this->devModeEnabled() || filled(config('services.payment.bank_account')),
                'promptpay' => $this->devModeEnabled() || filled(config('services.payment.promptpay_id')),
            ],
            'bank' => [
                'name' => config('services.payment.bank_name'),
                'accountName' => config('services.payment.bank_account_name'),
                'accountNumber' => config('services.payment.bank_account'),
            ],
            'promptpayId' => config('services.payment.promptpay_id'),
        ];
    }

    public function createIntent(Appointment $appointment, string $method): array
    {
        if ($method !== 'credit_card') {
            throw new \InvalidArgumentException('Payment Intent ใช้กับบัตรเครดิตเท่านั้น');
        }

        if (! $this->useStripe()) {
            if ($this->devModeEnabled()) {
                return [
                    'provider' => 'dev',
                    'devMode' => true,
                    'amount' => $appointment->amount,
                    'message' => 'โหมดทดสอบ — ใช้ dev-complete',
                ];
            }
            throw new \RuntimeException('Stripe ยังไม่ได้ตั้งค่า');
        }

        if ($appointment->amount < self::STRIPE_THB_MIN_AMOUNT) {
            throw new \RuntimeException(
                'ยอดชำระต้องไม่ต่ำกว่า ฿'.self::STRIPE_THB_MIN_AMOUNT.' สำหรับบัตรเครดิต (Stripe) — ยอดปัจจุบัน ฿'.$appointment->amount
            );
        }

        $stripe = new StripeClient(config('services.stripe.secret'));

        $existing = $appointment->payments()
            ->where('provider', 'stripe')
            ->where('status', 'pending')
            ->whereNotNull('stripe_payment_intent_id')
            ->latest()
            ->first();

        if ($existing) {
            try {
                $intent = $stripe->paymentIntents->retrieve($existing->stripe_payment_intent_id);
                if (in_array($intent->status, ['requires_payment_method', 'requires_confirmation', 'requires_action'], true)) {
                    return [
                        'paymentId' => $existing->id,
                        'provider' => 'stripe',
                        'clientSecret' => $intent->client_secret,
                        'paymentIntentId' => $intent->id,
                        'publishableKey' => config('services.stripe.key'),
                        'amount' => $appointment->amount,
                    ];
                }
            } catch (ApiErrorException) {
                // สร้าง intent ใหม่ด้านล่าง
            }
        }

        $payment = Payment::create([
            'appointment_id' => $appointment->id,
            'amount' => $appointment->amount,
            'currency' => 'thb',
            'method' => $method,
            'status' => 'pending',
            'provider' => 'stripe',
        ]);

        try {
            $intent = $stripe->paymentIntents->create([
                'amount' => $appointment->amount * 100,
                'currency' => 'thb',
                'payment_method_types' => ['card'],
                'metadata' => [
                    'appointment_id' => $appointment->id,
                    'payment_id' => $payment->id,
                ],
            ]);
        } catch (ApiErrorException $e) {
            $payment->update(['status' => 'failed']);
            throw new \RuntimeException($e->getMessage(), 0, $e);
        }

        $payment->update(['stripe_payment_intent_id' => $intent->id]);

        return [
            'paymentId' => $payment->id,
            'provider' => 'stripe',
            'clientSecret' => $intent->client_secret,
            'paymentIntentId' => $intent->id,
            'publishableKey' => config('services.stripe.key'),
            'amount' => $appointment->amount,
        ];
    }

    public function confirmStripe(Appointment $appointment, string $paymentIntentId): Appointment
    {
        $payment = $appointment->payments()
            ->where('stripe_payment_intent_id', $paymentIntentId)
            ->firstOrFail();

        if ($payment->status === 'succeeded') {
            return $appointment->fresh()->load(['clinic', 'service', 'therapist', 'promotion']);
        }

        if ($this->useStripe()) {
            $stripe = new StripeClient(config('services.stripe.secret'));
            $intent = $this->waitForPaymentSucceeded($stripe, $paymentIntentId);
            if ($intent->status !== 'succeeded') {
                throw new \RuntimeException(
                    'การชำระเงินยังไม่สำเร็จ (สถานะ: '.$intent->status.') — ลองกดยืนยันอีกครั้งหรือรอสักครู่'
                );
            }
        }

        $appointment = $this->markPaid($appointment, $payment);
        $this->notify->paymentConfirmed($appointment);
        SubmitAppointmentToPartnersJob::dispatch($appointment->id);

        return $appointment;
    }

    public function handlePaymentIntentSucceeded(string $paymentIntentId): void
    {
        $payment = Payment::where('stripe_payment_intent_id', $paymentIntentId)->first();
        if (! $payment || $payment->status === 'succeeded') {
            return;
        }

        $appointment = $payment->appointment;
        if (! $appointment || $appointment->status === 'confirmed') {
            return;
        }

        $appointment = $this->markPaid($appointment, $payment);
        $this->notify->paymentConfirmed($appointment);
        SubmitAppointmentToPartnersJob::dispatch($appointment->id);
    }

    public function handlePaymentIntentFailed(string $paymentIntentId): void
    {
        Payment::where('stripe_payment_intent_id', $paymentIntentId)
            ->where('status', 'pending')
            ->update(['status' => 'failed']);
    }

    public function submitManual(Appointment $appointment, string $method, string $reference, ?string $proofPath = null): Appointment
    {
        if (! in_array($method, ['transfer', 'promptpay'], true)) {
            throw new \InvalidArgumentException('ช่องทางไม่รองรับ');
        }

        if (! in_array($appointment->status, ['awaiting_payment', 'pending'], true)) {
            throw new \RuntimeException('การจองนี้ไม่สามารถชำระเงินได้');
        }

        $payment = Payment::create([
            'appointment_id' => $appointment->id,
            'amount' => $appointment->amount,
            'currency' => 'thb',
            'method' => $method,
            'payment_reference' => $reference,
            'proof_path' => $proofPath,
            'status' => 'pending_verification',
            'provider' => 'manual',
            'metadata' => ['submitted_at' => now()->toIso8601String()],
        ]);

        $appointment->update([
            'status' => 'awaiting_verification',
            'payment_method' => $method,
        ]);

        $appointment = $appointment->fresh()->load(['clinic', 'service', 'therapist', 'promotion']);
        $this->notify->awaitingVerification($appointment, $payment);

        return $appointment;
    }

    public function confirmManual(Appointment $appointment): Appointment
    {
        $payment = $appointment->payments()
            ->where('status', 'pending_verification')
            ->latest()
            ->firstOrFail();

        $appointment = $this->markPaid($appointment, $payment);
        $this->notify->paymentConfirmed($appointment);
        SubmitAppointmentToPartnersJob::dispatch($appointment->id);

        return $appointment->load(['clinic', 'service', 'therapist', 'promotion']);
    }

    public function devComplete(Appointment $appointment, string $method): Appointment
    {
        if (! $this->devModeEnabled()) {
            throw new \RuntimeException('Dev payment ไม่เปิดใช้งาน — ใช้ Stripe หรือช่องทางโอนเงิน');
        }

        $payment = $appointment->payments()->latest()->first();
        if (! $payment) {
            $payment = Payment::create([
                'appointment_id' => $appointment->id,
                'amount' => $appointment->amount,
                'currency' => 'thb',
                'method' => $method,
                'status' => 'pending',
                'provider' => 'dev',
            ]);
        }

        $appointment = $this->markPaid($appointment, $payment, $method);
        $this->notify->paymentConfirmed($appointment);
        SubmitAppointmentToPartnersJob::dispatch($appointment->id);

        return $appointment;
    }

    protected function markPaid(Appointment $appointment, Payment $payment, ?string $method = null): Appointment
    {
        $method = $method ?? $payment->method;

        $payment->update([
            'status' => 'succeeded',
            'method' => $method,
            'paid_at' => now(),
        ]);

        $appointment->update([
            'status' => 'confirmed',
            'payment_method' => $method,
            'paid_at' => now(),
            'slot_locked_until' => null,
        ]);

        if ($appointment->promotion_id) {
            $appointment->promotion?->increment('used_count');
        }

        $appointment->timeSlot?->update(['available' => false]);

        return $appointment->fresh()->load(['clinic', 'service', 'therapist', 'promotion']);
    }

    /**
     * Stripe อาจยังเป็น processing ทันทีหลัง confirm ฝั่ง browser
     */
    protected function waitForPaymentSucceeded(StripeClient $stripe, string $paymentIntentId): \Stripe\PaymentIntent
    {
        $intent = $stripe->paymentIntents->retrieve($paymentIntentId);

        for ($i = 0; $i < 12; $i++) {
            if ($intent->status === 'succeeded') {
                return $intent;
            }
            if (in_array($intent->status, ['canceled', 'requires_payment_method'], true)) {
                return $intent;
            }
            usleep(500_000);
            $intent = $stripe->paymentIntents->retrieve($paymentIntentId);
        }

        return $intent;
    }

    public function devModeEnabled(): bool
    {
        return (bool) env('PAYMENT_DEV_MODE', false);
    }

    protected function useStripe(): bool
    {
        return ! $this->devModeEnabled()
            && filled(config('services.stripe.secret'))
            && str_starts_with((string) config('services.stripe.secret'), 'sk_');
    }

    public function stripeReady(): bool
    {
        return $this->useStripe();
    }

    public function submitToPartners(Appointment $appointment): ?array
    {
        if (! config('services.partners.enabled')) {
            return null;
        }

        $url = rtrim(config('services.partners.url'), '/').'/api/v1/partners/submit';

        $response = Http::withToken(config('services.partners.key'))
            ->timeout(15)
            ->post($url, [
                'reference' => $appointment->id,
                'clinic_id' => $appointment->clinic_id,
                'service_id' => $appointment->service_id,
                'therapist_id' => $appointment->therapist_id,
                'date' => $appointment->date->format('Y-m-d'),
                'time_slot_id' => $appointment->time_slot_id,
                'customer' => [
                    'name' => $appointment->customer_name,
                    'phone' => $appointment->customer_phone,
                    'email' => $appointment->customer_email,
                ],
                'amount' => $appointment->amount,
                'status' => $appointment->status,
            ]);

        if ($response->successful()) {
            $ref = $response->json('reference') ?? $response->json('id');
            $appointment->update(['partner_reference' => $ref]);

            return $response->json();
        }

        Log::warning('Partners API failed', ['body' => $response->body()]);

        return ['error' => $response->body(), 'status' => $response->status()];
    }
}
