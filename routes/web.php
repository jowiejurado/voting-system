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
use App\Http\Controllers\Auth\VoterAuthController;
use App\Http\Controllers\Voter\BallotController;
use App\Http\Controllers\Voter\VoterFaceController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');
Route::apiResource('admin', AdminController::class)->except('show');

Route::prefix('admin')->name('admin.')->group(function () {
	Route::get('login', [AdminAuthController::class, 'showLogin'])->name('login');
	Route::post('login', [AdminAuthController::class, 'login'])->name('login.submit');
	Route::get('otp', [AdminAuthController::class, 'showOtp'])->name('otp');
	Route::post('otp', [AdminAuthController::class, 'verifyOtp'])->name('otp.verify');
	Route::get('logout', [AdminAuthController::class, 'logout'])->name('logout');

	Route::post('send-otp', [AdminAuthController::class, 'sendOtp'])->name('send-otp');
	Route::match(['post', 'put'], 'change-password', [AdminAuthController::class, 'changePassword'])->name('change-password');

	// Admin password reset
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

// Voter auth and panel
Route::prefix('voter')->name('voter.')->group(function () {
	Route::get('login', [VoterAuthController::class, 'showLogin'])->name('login');
	Route::post('login', [VoterAuthController::class, 'login'])->name('login.submit');

	Route::get('otp', [VoterAuthController::class, 'showOtp'])->name('otp');
	Route::post('otp', [VoterAuthController::class, 'verifyOtp'])->name('otp.verify');

  Route::get('/verify-method', [VoterAuthController::class, 'showVerifyMethod'])->name('verify.method');
  Route::get('/security', [VoterAuthController::class, 'showSecurityQuestion'])->name('security.show');
  Route::post('/security', [VoterAuthController::class, 'verifySecurityAnswer'])->name('security.verify');

	Route::post('logout', [VoterAuthController::class, 'logout'])->name('logout');

	Route::post('send-otp', [VoterAuthController::class, 'sendOtp'])->name('send-otp');
	Route::match(['post', 'put'], 'change-password', [VoterAuthController::class, 'changePassword'])->name('change-password');

	// Voter password reset
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

