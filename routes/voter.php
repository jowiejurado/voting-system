<?php

use App\Http\Controllers\Auth\VoterAuthController;
use App\Http\Controllers\Voter\BallotController;
use App\Http\Controllers\Voter\VoterFaceController;
use Illuminate\Support\Facades\Route;

// Landing: redirect root to login (voter panel entry)
Route::get('/', fn () => redirect()->route('voter.login'))->name('voter.home');

Route::name('voter.')->group(function () {
    Route::get('login', [VoterAuthController::class, 'showLogin'])->name('login');
    Route::post('login', [VoterAuthController::class, 'login'])->name('login.submit');

    Route::get('otp', [VoterAuthController::class, 'showOtp'])->name('otp');
    Route::post('otp', [VoterAuthController::class, 'verifyOtp'])->name('otp.verify');

    Route::get('verify-method', [VoterAuthController::class, 'showVerifyMethod'])->name('verify.method');
    Route::get('security', [VoterAuthController::class, 'showSecurityQuestion'])->name('security.show');
    Route::post('security', [VoterAuthController::class, 'verifySecurityAnswer'])->name('security.verify');

    Route::post('logout', [VoterAuthController::class, 'logout'])->name('logout');

    Route::post('send-otp', [VoterAuthController::class, 'sendOtp'])->name('send-otp');
    Route::match(['post', 'put'], 'change-password', [VoterAuthController::class, 'changePassword'])->name('change-password');

    Route::get('forgot-password', [VoterAuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('forgot-password', [VoterAuthController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('reset-password/{token}', [VoterAuthController::class, 'showResetForm'])->name('password.reset');
    Route::post('reset-password', [VoterAuthController::class, 'resetPassword'])->name('password.update');

    Route::middleware(['voter'])->group(function () {
        Route::get('ballot', [BallotController::class, 'showBallot'])->name('ballot');
        Route::post('ballot/submit', [BallotController::class, 'submit'])->name('ballot.submit');
        Route::get('face', [VoterFaceController::class, 'show'])->name('face');
        Route::post('face/verify', [VoterFaceController::class, 'verify'])->name('face.verify');
    });
});
