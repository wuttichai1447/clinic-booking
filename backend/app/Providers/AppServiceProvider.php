<?php

namespace App\Providers;

use App\Models\Appointment;
use App\Models\BookingNotification;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::defaultView('vendor.pagination.admin');

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(120)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });

        RateLimiter::for('booking', function (Request $request) {
            return Limit::perMinute(15)->by($request->user()?->id ?: $request->ip());
        });

        View::composer('admin.layout', function ($view) {
            $view->with([
                'awaitingVerificationCount' => Appointment::where('status', 'awaiting_verification')->count(),
                'unreadBookingNotificationsCount' => BookingNotification::whereNull('read_at')->count(),
            ]);
        });
    }
}
