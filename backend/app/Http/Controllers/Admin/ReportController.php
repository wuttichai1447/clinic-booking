<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function export(Request $request): StreamedResponse
    {
        $from = $request->date('from') ?? now()->subDays(30);
        $to = $request->date('to') ?? now();

        $rows = Appointment::with(['clinic', 'service', 'therapist'])
            ->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()])
            ->orderByDesc('created_at')
            ->get();

        $filename = 'appointments-'.$from->format('Y-m-d').'-'.$to->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, [
                'id', 'created_at', 'status', 'customer_name', 'customer_phone',
                'clinic', 'service', 'therapist', 'date', 'time_slot', 'amount', 'paid_at',
            ]);
            foreach ($rows as $a) {
                fputcsv($out, [
                    $a->id,
                    $a->created_at?->toDateTimeString(),
                    $a->status,
                    $a->customer_name,
                    $a->customer_phone,
                    $a->clinic?->name,
                    $a->service?->name,
                    $a->therapist?->name,
                    $a->date->format('Y-m-d'),
                    $a->time_slot_id,
                    $a->amount,
                    $a->paid_at?->toDateTimeString(),
                ]);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
