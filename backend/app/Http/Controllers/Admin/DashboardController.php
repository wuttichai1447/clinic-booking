<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Promotion;
use App\Models\Service;
use App\Models\Therapist;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $statusCounts = Appointment::query()
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $last7Days = collect(range(6, 0))->map(function ($daysAgo) {
            $date = now()->subDays($daysAgo)->format('Y-m-d');
            $count = Appointment::whereDate('created_at', $date)->count();

            return ['date' => $date, 'count' => $count];
        });

        $revenueConfirmed = Appointment::where('status', 'confirmed')->sum('amount');

        return view('admin.dashboard', [
            'stats' => [
                'clinics' => Clinic::count(),
                'services' => Service::count(),
                'therapists' => Therapist::count(),
                'appointments' => Appointment::count(),
                'customers' => User::where('role', 'customer')->count(),
                'promotions' => Promotion::where('is_active', true)->count(),
                'pending' => Appointment::whereIn('status', ['pending', 'awaiting_payment', 'awaiting_verification'])->count(),
                'confirmed' => Appointment::where('status', 'confirmed')->count(),
                'revenue' => $revenueConfirmed,
            ],
            'statusCounts' => $statusCounts,
            'chartLabels' => $last7Days->pluck('date')->map(fn ($d) => date('d/m', strtotime($d)))->values(),
            'chartData' => $last7Days->pluck('count')->values(),
            'recent' => Appointment::with(['clinic', 'service', 'therapist'])
                ->orderByDesc('created_at')
                ->limit(10)
                ->get(),
        ]);
    }
}
