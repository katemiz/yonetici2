<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('resident-login', [\App\Http\Controllers\Auth\ResidentSessionController::class, 'create'])
        ->name('resident.login');
    Route::post('resident-login', [\App\Http\Controllers\Auth\ResidentSessionController::class, 'store'])
        ->name('resident.login.store');

    Route::get('register', [RegisteredUserController::class, 'create'])
                ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
                ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
                ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
                ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
                ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
                ->name('password.update');
});

Route::middleware('resident')->group(function () {
    Route::get('resident-dashboard', function (Illuminate\Http\Request $request) {
        $resident = \App\Models\Sakin::findOrFail($request->session()->get('resident_id'));
        $building = \App\Models\Bina::findOrFail($request->session()->get('resident_bina_id'));

        abort_unless($resident->bina_id === $building->id && $resident->is_active, 403);

        return view('resident.dashboard', compact('resident', 'building'));
    })->name('resident.dashboard');
    Route::get('resident-status/{section}', [\App\Http\Controllers\ResidentStatusController::class, 'show'])
        ->where('section', 'ozet|alacaklar|gelirler|giderler')
        ->name('resident.status');
    Route::post('resident-logout', [\App\Http\Controllers\Auth\ResidentSessionController::class, 'destroy'])
        ->name('resident.logout');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', [EmailVerificationPromptController::class, '__invoke'])
                ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
                ->middleware(['signed', 'throttle:6,1'])
                ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
                ->middleware('throttle:6,1')
                ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
                ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
                ->name('logout');
});
