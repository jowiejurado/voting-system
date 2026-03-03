<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminFaceController;
use App\Http\Controllers\Admin\CandidateController as AdminCandidateController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PositionController as AdminPositionController;
use App\Http\Controllers\Admin\VoteController as AdminVoteController;
use App\Http\Controllers\Admin\VoterController as AdminVoterController;
use App\Http\Controllers\Admin\ElectionController as AdminElectionController;
use App\Http\Controllers\Admin\ArchiveElectionController as AdminArchiveController;
use App\Http\Controllers\Auth\AdminAuthController;
use Illuminate\Support\Facades\Route;

// Landing: redirect root to login (admin panel entry)
Route::get('/', fn () => redirect()->route('admin.login'))->name('admin.home');

// Admin user management (route names: admin.index, admin.store, etc.)
Route::apiResource('admin', AdminController::class)->except('show');

Route::name('admin.')->group(function () {
    Route::get('login', [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AdminAuthController::class, 'login'])->name('login.submit');
    Route::get('otp', [AdminAuthController::class, 'showOtp'])->name('otp');
    Route::post('otp', [AdminAuthController::class, 'verifyOtp'])->name('otp.verify');
    Route::get('logout', [AdminAuthController::class, 'logout'])->name('logout');

    Route::post('send-otp', [AdminAuthController::class, 'sendOtp'])->name('send-otp');
    Route::match(['post', 'put'], 'change-password', [AdminAuthController::class, 'changePassword'])->name('change-password');

    Route::get('forgot-password', [AdminAuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('forgot-password', [AdminAuthController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('reset-password/{token}', [AdminAuthController::class, 'showResetForm'])->name('password.reset');
    Route::post('reset-password', [AdminAuthController::class, 'resetPassword'])->name('password.update');

    Route::middleware(['admin'])->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::apiResource('voters', AdminVoterController::class);
        Route::apiResource('positions', AdminPositionController::class);
        Route::apiResource('candidates', AdminCandidateController::class);
        Route::apiResource('elections', AdminElectionController::class);
        Route::get('archives', [AdminArchiveController::class, 'index'])->name('archives.index');
        Route::get('archives/{election}', [AdminArchiveController::class, 'show'])->name('archives.show');
        Route::get('votes', [AdminVoteController::class, 'index'])->name('votes.index');
        Route::get('voter-status', [AdminVoteController::class, 'voterStatus'])->name('voter-status.index');
        Route::get('face', [AdminFaceController::class, 'show'])->name('face');
        Route::post('face/verify', [AdminFaceController::class, 'verify'])->name('face.verify');
    });
});
