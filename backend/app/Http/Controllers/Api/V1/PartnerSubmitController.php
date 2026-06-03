<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PartnerSubmitController extends Controller
{
    /**
     * Endpoint ตาม Partners API Document: api/v1/partners/submit
     * รับข้อมูลการจองจาก partner / frontend
     */
    public function submit(Request $request): JsonResponse
    {
        $data = $request->validate([
            'clinic_id' => 'required|string|exists:clinics,id',
            'service_id' => 'required|string|exists:services,id',
            'therapist_id' => 'required|string|exists:therapists,id',
            'date' => 'required|date',
            'time_slot_id' => 'required|string',
            'customer' => 'required|array',
            'customer.name' => 'required|string',
            'customer.phone' => 'required|string',
            'customer.email' => 'nullable|email',
            'amount' => 'nullable|integer',
            'notes' => 'nullable|string',
        ]);

        $service = \App\Models\Service::findOrFail($data['service_id']);

        $appointment = Appointment::create([
            'clinic_id' => $data['clinic_id'],
            'service_id' => $data['service_id'],
            'therapist_id' => $data['therapist_id'],
            'date' => $data['date'],
            'time_slot_id' => $data['time_slot_id'],
            'customer_name' => $data['customer']['name'],
            'customer_phone' => $data['customer']['phone'],
            'customer_email' => $data['customer']['email'] ?? null,
            'notes' => $data['notes'] ?? null,
            'status' => 'awaiting_payment',
            'amount' => $data['amount'] ?? $service->price,
            'partner_reference' => 'PTR-'.Str::upper(Str::random(8)),
        ]);

        return response()->json([
            'success' => true,
            'reference' => $appointment->partner_reference,
            'appointment_id' => $appointment->id,
            'status' => $appointment->status,
            'payment_url' => config('app.frontend_url').'/pay/'.$appointment->id,
        ], 201);
    }
}
