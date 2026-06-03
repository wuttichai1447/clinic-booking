<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Concerns\ResolvesApiUser;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ReceiptController extends Controller
{
    use ResolvesApiUser;

    public function show(Request $request, Appointment $appointment): Response
    {
        $user = $this->apiUser($request);
        if ($user?->isCustomer()) {
            if ($appointment->user_id && $appointment->user_id !== $user->id) {
                abort(403);
            }
        }

        if (! in_array($appointment->status, ['confirmed', 'completed'], true)) {
            abort(422, 'ออกใบเสร็จได้เมื่อชำระเงินแล้วเท่านั้น');
        }

        $appointment->load(['clinic', 'service', 'therapist', 'promotion']);

        $html = view('receipts.appointment', compact('appointment'))->render();

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Content-Disposition' => 'inline; filename="receipt-'.$appointment->id.'.html"',
        ]);
    }
}
