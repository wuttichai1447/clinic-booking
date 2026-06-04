<?php

use App\Http\Controllers\Admin\AppointmentController as AdminAppointmentController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\ClinicController as AdminClinicController;
use App\Http\Controllers\Admin\ClinicHolidayController;
use App\Http\Controllers\Admin\BookingNotificationController;
use App\Http\Controllers\Admin\StaffLeaveController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\Admin\PromotionController as AdminPromotionController;
use App\Http\Controllers\Admin\TherapistController as AdminTherapistController;
use Illuminate\Support\Facades\Route;

Route::get('/web-session-test', function () {
    return response()->json([
        'ok' => true,
        'session' => session()->getId(),
    ]);
});

Route::get('/', function () {
    return response()->json([
        'name' => 'Clinic Booking API',
        'version' => '1.0',
        'admin' => url('/admin'),
        'api' => url('/api/v1'),
        'frontend' => config('app.frontend_url'),
    ]);
});

Route::get('/internal/cron/reminders', function () {
    $key = request()->header('X-Cron-Key') ?? request('key');
    if (! filled(config('app.cron_key')) || ! hash_equals((string) config('app.cron_key'), (string) $key)) {
        abort(403, 'Forbidden');
    }

    \Illuminate\Support\Facades\Artisan::call('booking:send-reminders');

    return response()->json([
        'ok' => true,
        'output' => trim(\Illuminate\Support\Facades\Artisan::output()),
    ]);
})->middleware('throttle:10,1');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthController::class, 'showLogin'])
            ->name('login')
            ->withoutMiddleware(['guest']);
        Route::post('login', [AuthController::class, 'login'])->name('login.submit');
    });

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('clinics', AdminClinicController::class)->except(['show']);
        Route::resource('holidays', ClinicHolidayController::class)->except(['show']);
        Route::resource('staff-leaves', StaffLeaveController::class)->except(['show']);
        Route::resource('services', AdminServiceController::class)->except(['show']);
        Route::resource('therapists', AdminTherapistController::class)->except(['show']);
        Route::resource('promotions', AdminPromotionController::class)->except(['show']);
        Route::get('booking-notifications', [BookingNotificationController::class, 'index'])->name('booking-notifications.index');
        Route::post('booking-notifications/read-all', [BookingNotificationController::class, 'markAllRead'])->name('booking-notifications.read-all');
        Route::post('booking-notifications/{booking_notification}/read', [BookingNotificationController::class, 'markRead'])->name('booking-notifications.read');

        Route::resource('appointments', AdminAppointmentController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
        Route::post('appointments/{appointment}/confirm-payment', [AdminAppointmentController::class, 'confirmPayment'])->name('appointments.confirm-payment');
        Route::post('appointments/{appointment}/cancel', [AdminAppointmentController::class, 'cancel'])->name('appointments.cancel');
        Route::post('appointments/{appointment}/reschedule', [AdminAppointmentController::class, 'reschedule'])->name('appointments.reschedule');
        Route::get('reports/export', [ReportController::class, 'export'])->name('reports.export');
    });
});
