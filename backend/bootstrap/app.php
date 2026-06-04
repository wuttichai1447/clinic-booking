<?php

use App\Http\Controllers\Admin\AuthController;
use App\Models\Clinic;
use App\Models\User;
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

                    $sessionTest = 'skipped';
                    try {
                        session()->put('health_check', 1);
                        $sessionTest = session()->get('health_check') === 1 ? 'ok' : 'fail';
                    } catch (\Throwable $e) {
                        $sessionTest = $e->getMessage();
                    }

                    $appKeyStatus = 'unknown';
                    try {
                        app('encrypter');
                        $appKeyStatus = 'ok';
                    } catch (\Throwable $e) {
                        $appKeyStatus = $e->getMessage();
                    }

                    $adminUser = User::where('role', 'admin')->first();

                    return response()->json([
                        'database' => 'ok',
                        'clinics' => Clinic::count(),
                        'admin_users' => User::whereIn('role', ['admin', 'staff'])->count(),
                        'admin_email' => $adminUser?->email,
                        'sessions_table' => \Illuminate\Support\Facades\Schema::hasTable('sessions'),
                        'session_driver' => config('session.driver'),
                        'cache_store' => config('cache.default'),
                        'session_test' => $sessionTest,
                        'app_key' => $appKeyStatus,
                    ]);
                } catch (\Throwable $e) {
                    return response()->json([
                        'database' => 'error',
                        'message' => $e->getMessage(),
                    ], 500);
                }
            });

            // Render: web middleware breaks some pages; serve without middleware stack
            Route::get('/', function () {
                return response()->json([
                    'name' => 'Clinic Booking API',
                    'version' => '1.0',
                    'health' => url('/up'),
                    'admin' => url('/admin/login'),
                    'api' => url('/api/v1'),
                    'frontend' => config('app.frontend_url'),
                ]);
            });

            Route::middleware('session')->group(function () {
                Route::get('/admin/login', [AuthController::class, 'showLogin'])->name('admin.login');
                Route::post('/admin/login', [AuthController::class, 'login'])->name('admin.login.submit');
            });
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');
        $middleware->web(
            remove: [
                \Illuminate\Cookie\Middleware\EncryptCookies::class,
                \Illuminate\Session\Middleware\StartSession::class,
                \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            ],
            prepend: [
                \App\Http\Middleware\EnsureSessionStarted::class,
                \App\Http\Middleware\VerifyFormCsrf::class,
            ],
        );
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
            'session' => \App\Http\Middleware\EnsureSessionStarted::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
