<?php

namespace App\Jobs;

use App\Models\Appointment;
use App\Services\PaymentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SubmitAppointmentToPartnersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 4;

    public array $backoff = [15, 60, 180, 600];

    public function __construct(public string $appointmentId) {}

    public function handle(PaymentService $payments): void
    {
        $appointment = Appointment::find($this->appointmentId);
        if ($appointment) {
            $payments->submitToPartners($appointment);
        }
    }
}
