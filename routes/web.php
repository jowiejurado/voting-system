<?php

use App\Http\Controllers\Auth\UnifiedAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('auth.login'))->name('home');

Route::prefix('auth')->name('auth.')->group(function () {
	Route::get('login', [UnifiedAuthController::class, 'showLogin'])->name('login');
	Route::post('login', [UnifiedAuthController::class, 'login'])->name('login.submit');
	Route::get('forgot-password', [UnifiedAuthController::class, 'showForgotPassword'])->name('password.request');
	Route::post('forgot-password', [UnifiedAuthController::class, 'sendForgotPasswordEmail'])->name('password.email');
});
