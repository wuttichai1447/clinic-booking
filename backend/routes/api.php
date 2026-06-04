<?php

use App\Http\Controllers\Api\V1\AppointmentController;
use App\Http\Controllers\Api\V1\AppointmentLifecycleController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\ReceiptController;
use App\Http\Controllers\Api\V1\ClinicController;
use App\Http\Controllers\Api\V1\PartnerSubmitController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\PromotionController;
use App\Http\Controllers\Api\V1\ServiceController;
use App\Http\Controllers\Api\V1\SlotController;
use App\Http\Controllers\Api\V1\SocialAuthController;
use App\Http\Controllers\Api\V1\StripeWebhookController;
use App\Http\Controllers\Api\V1\TherapistController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/auth/register', [AuthController::class, 'register'])->middleware('throttle:auth');
    Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:auth');

    Route::get('/auth/google/redirect', [SocialAuthController::class, 'redirectGoogle']);
    Route::get('/auth/google/callback', [SocialAuthController::class, 'callbackGoogle']);
    Route::get('/auth/facebook/redirect', [SocialAuthController::class, 'redirectFacebook']);
    Route::get('/auth/facebook/callback', [SocialAuthController::class, 'callbackFacebook']);

    Route::get('/clinics', [ClinicController::class, 'index']);
    Route::get('/services', [ServiceController::class, 'index']);
    Route::get('/therapists', [TherapistController::class, 'index']);
    Route::get('/slots', [SlotController::class, 'index']);

    Route::post('/promotions/validate', [PromotionController::class, 'validateCode']);

    Route::get('/payments/config', [PaymentController::class, 'config']);

    Route::get('/appointments', [AppointmentController::class, 'index']);
    Route::post('/appointments', [AppointmentController::class, 'store'])->middleware('throttle:booking');
    Route::get('/appointments/{appointment}', [AppointmentController::class, 'show']);
    Route::get('/appointments/{appointment}/invoice', [AppointmentController::class, 'invoice']);
    Route::get('/appointments/{appointment}/receipt', [ReceiptController::class, 'show']);
    Route::get('/appointments/{appointment}/refund-policy', [AppointmentLifecycleController::class, 'refundPolicy']);

    Route::post('/appointments/{appointment}/payments/intent', [PaymentController::class, 'createIntent']);
    Route::post('/appointments/{appointment}/payments/confirm', [PaymentController::class, 'confirm']);
    Route::post('/appointments/{appointment}/payments/submit-manual', [PaymentController::class, 'submitManual']);
    Route::post('/appointments/{appointment}/payments/dev-complete', [PaymentController::class, 'devComplete']);

    Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle']);

    Route::post('/partners/submit', [PartnerSubmitController::class, 'submit']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/me/appointments', [AppointmentController::class, 'mine']);
        Route::get('/me/profile', [ProfileController::class, 'show']);
        Route::patch('/me/profile', [ProfileController::class, 'update']);
        Route::post('/me/password', [ProfileController::class, 'updatePassword']);
        Route::post('/appointments/{appointment}/apply-promo', [PromotionController::class, 'applyToAppointment']);
        Route::post('/appointments/{appointment}/cancel', [AppointmentLifecycleController::class, 'cancel']);
        Route::post('/appointments/{appointment}/reschedule', [AppointmentLifecycleController::class, 'reschedule']);
    });
});
