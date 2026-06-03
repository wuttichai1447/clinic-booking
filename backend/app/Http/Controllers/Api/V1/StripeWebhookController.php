<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessStripeWebhookEventJob;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function __construct(protected PaymentService $payments) {}

    public function handle(Request $request): Response
    {
        $secret = config('services.stripe.webhook_secret');
        if (! filled($secret)) {
            return response('Webhook secret not configured', 503);
        }

        $payload = $request->getContent();
        $sig = $request->header('Stripe-Signature');

        try {
            $event = Webhook::constructEvent($payload, $sig, $secret);
        } catch (SignatureVerificationException|\UnexpectedValueException $e) {
            return response('Invalid signature', 400);
        }

        if (in_array($event->type, ['payment_intent.succeeded', 'payment_intent.payment_failed'], true)) {
            ProcessStripeWebhookEventJob::dispatch($event->type, $event->data->object->id);
        }

        return response('OK', 200);
    }
}
