<?php

use App\Http\Controllers\Auth\UnifiedAuthController;
use App\Http\Controllers\Auth\VoterAuthController;
use App\Http\Controllers\Voter\BallotController;
use App\Http\Controllers\Voter\VoterFaceController;
use Illuminate\Support\Facades\Route;

// Landing: redirect root to shared sign-in
Route::get('/', fn () => redirect()->route('auth.login'))->name('voter.home');

Route::name('voter.')->group(function () {
    Route::get('verify-method', [UnifiedAuthController::class, 'showVerifyMethod'])->name('verify.method');
    Route::get('verify/begin/{method}', [UnifiedAuthController::class, 'beginVerification'])->where('method', 'otp|face')->name('verify.begin');

    Route::get('otp', [UnifiedAuthController::class, 'showOtp'])->name('otp');
    Route::post('otp', [UnifiedAuthController::class, 'verifyOtp'])->name('otp.verify');

    Route::match(['get', 'post'], 'logout', [UnifiedAuthController::class, 'logout'])->name('logout');

    Route::post('send-otp', [VoterAuthController::class, 'sendOtp'])->name('send-otp');

    Route::get('forgot-password', fn () => redirect()->route('auth.password.request'))->name('password.request');
    Route::post('forgot-password', [UnifiedAuthController::class, 'sendForgotPasswordEmail'])->name('password.email');
    Route::get('reset-password/{token}', [VoterAuthController::class, 'showResetForm'])->name('password.reset');
    Route::post('reset-password', [VoterAuthController::class, 'resetPassword'])->name('password.update');

    Route::middleware(['voter'])->group(function () {
        Route::get('security', [UnifiedAuthController::class, 'showSecurity'])->name('security.show');
        Route::post('security', [UnifiedAuthController::class, 'verifySecurityAnswer'])->name('security.verify');
        Route::get('face', [VoterFaceController::class, 'show'])->name('face');
        Route::post('face/verify', [VoterFaceController::class, 'verify'])->name('face.verify');
    });

    Route::middleware(['voter', 'second.factor'])->group(function () {
        Route::match(['post', 'put'], 'change-password', [VoterAuthController::class, 'changePassword'])->name('change-password');
        Route::get('ballot', [BallotController::class, 'showBallot'])->name('ballot');
        Route::post('ballot/submit', [BallotController::class, 'submit'])->name('ballot.submit');
    });
});
