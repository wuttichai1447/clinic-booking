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
        $databaseUrl = env('DATABASE_URL');
        if (is_string($databaseUrl) && str_starts_with($databaseUrl, 'postgres://')) {
            $normalized = 'postgresql://'.substr($databaseUrl, 11);
            $_ENV['DATABASE_URL'] = $normalized;
            putenv('DATABASE_URL='.$normalized);
        }
    }

    public function boot(): void
    {
        // Render: database cache breaks rate limiting; force array (ignore stale Render env)
        config(['cache.default' => 'array']);

        // Database sessions work via session(); avoid Laravel StartSession middleware on Render
        if (env('DATABASE_URL')) {
            config(['session.driver' => 'database']);
        }

        $appUrl = (string) env('APP_URL', '');
        if (str_starts_with($appUrl, 'https://')) {
            config(['session.secure' => true]);
        }

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
            $frontendUrl = rtrim((string) config('app.frontend_url', ''), '/');
            $frontendReady = filled($frontendUrl)
                && ! preg_match('#placeholder\.#i', $frontendUrl)
                && (! app()->environment('production') || ! preg_match('#localhost|127\.0\.0\.1#i', $frontendUrl));

            try {
                $view->with([
                    'awaitingVerificationCount' => Appointment::where('status', 'awaiting_verification')->count(),
                    'unreadBookingNotificationsCount' => BookingNotification::whereNull('read_at')->count(),
                    'frontendUrl' => $frontendUrl ?: 'http://localhost:3000',
                    'frontendReady' => $frontendReady,
                ]);
            } catch (\Throwable) {
                $view->with([
                    'awaitingVerificationCount' => 0,
                    'unreadBookingNotificationsCount' => 0,
                    'frontendUrl' => $frontendUrl ?: 'http://localhost:3000',
                    'frontendReady' => $frontendReady,
                ]);
            }
        });
    }
}
