<?php

use App\Models\Clinic;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::get('/api/health/db', function () {
                try {
                    DB::connection()->getPdo();

                    return response()->json([
                        'database' => 'ok',
                        'clinics' => Clinic::count(),
                    ]);
                } catch (\Throwable $e) {
                    return response()->json([
                        'database' => 'error',
                        'message' => $e->getMessage(),
                    ], 500);
                }
            });

            Route::get('/admin/ping', fn () => response()->json(['admin' => 'ok']));
            Route::get('/admin/login-test', fn () => view('admin.login'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');
        $middleware->redirectGuestsTo('/admin/login');
        $middleware->redirectUsersTo('/admin');
        // Rate limiting disabled on Render free tier (database/file cache caused HTTP 500)
        // Public booking API is stateless JSON — no SPA session/CSRF on /api/v1/*
        $middleware->validateCsrfTokens(except: [
            'api/*',
        ]);
        $middleware->api(append: [
            \App\Http\Middleware\AuditApiRequests::class,
        ]);
        $middleware->alias([
            'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
            'admin' => \App\Http\Middleware\EnsureAdminRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
