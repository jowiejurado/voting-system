<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OtpService;
use App\Services\RecaptchaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use App\Mail\ResetPasswordMail;

class AdminAuthController extends Controller
{
	public function __construct(private OtpService $otpService) {}

	public function showLogin()
	{
		return view('admin.auth.login');
	}

	public function login(Request $request)
	{
		$request->validate([
			'adminId' => ['required'],
			'password' => ['required'],
			'g-recaptcha-response' => ['required', 'captcha'],
    ], [
			'g-recaptcha-response.required' => 'Please confirm you are not a robot.',
			'g-recaptcha-response.captcha'  => 'reCAPTCHA verification failed. Please try again.',
    ]);

		if (!Auth::attempt(['admin_id' => $request->adminId, 'password' => $request->password])) {
			return back()->with([
				'error' => 'Invalid details',
				'buttonText' => 'TRY AGAIN',
			])->withInput();
		}

		$request->session()->regenerate();
		session(['otp_verified' => false]);
		$this->otpService->sendOTP(Auth::user(), 'login');

		$user = $request->user();
		$user->forceFill(['last_signed_in' => now('Asia/Manila')])->save();

		return redirect()->route('admin.otp')->with([
			'success' => 'Valid Details',
			'buttonText' => 'Proceed',
		]);
	}

	public function showOtp()
	{
		return view('admin.auth.otp');
	}

	public function verifyOtp(Request $request)
	{
		$request->validate([
			'code' => 'required|string'
		]);

		$user = Auth::user();

		if ($user && $this->otpService->verifyOtp($user, $request->code)) {
		// if ($user) {
			session(['otp_verified' => true]);
			$route = Auth::user()->type === 'system-admin' ? 'admin.dashboard' : 'admin.face';
			return redirect()->route($route)->with([
				'success' => 'Code Confirmed',
				'buttonText' => 'Proceed'
			]);
		}

		return back()->with([
			'error' => 'Invalid Code',
			'buttonText' => 'TRY AGAIN',
		]);
	}

	public function logout(Request $request)
	{
		$user = $request->user();
		$user->forceFill(['last_signed_out' => now('Asia/Manila')])->save();
		Auth::logout();
		$request->session()->invalidate();
		$request->session()->regenerateToken();
		return redirect()->route('admin.login');
	}

	public function sendOtp(Request $request)
	{
		$user = Auth::user();

		if (!$user) {
			return back()->with([
				'error' => 'Failed to send OTP',
				'buttonText' => 'TRY AGAIN',
			]);
		}

		$this->otpService->sendOTP($user, 'change-password');

		return back()->with([
			'success' => 'OTP has been sent',
			'buttonText' => 'Proceed'
		]);
	}

	public function changePassword(Request $request)
	{
		$request->validate([
			'current_password' => 'required',
			'password'         => 'required',
			'otp'              => 'required|digits:6',
		]);

		$user = $request->user();

		if (!Hash::check($request->current_password, $user->password)) {
			return back()->with([
				'error' => 'Invalid Details',
				'buttonText' => 'TRY AGAIN',
				'__action' => 'change-password'
			]);
		}

		if (!$this->otpService->verifyOtp($user, $request->otp, 'change-password')) {
			return back()->with([
				'error' => 'Invalid Code',
				'buttonText' => 'TRY AGAIN',
				'__action' => 'change-password'
			]);
		}

		$user->forceFill(['password' => Hash::make($request->password)])->save();

		return redirect()->route('admin.dashboard')->with([
			'success' => 'Password updated',
			'buttonText' => 'Proceed'
		]);
	}

	/**
	 * Show the form to request a password reset email (Admin).
	 */
	public function showForgotPassword()
	{
		return view('admin.auth.forgot-password');
	}

	/**
	 * Send password reset link email (Admin).
	 */
	public function sendResetLinkEmail(Request $request)
	{
		$request->validate(['email' => ['required', 'email']]);

		$user = User::where('email', $request->email)
			->whereIn('type', ['admin', 'system-admin'])
			->first();

		// For privacy, don't reveal if user doesn't exist
		if ($user) {
			$token = Password::broker('users')->createToken($user);
			$resetUrl = route('admin.password.reset', [
				'token' => $token,
				'email' => $user->email,
			]);
			Mail::to($user->email)->send(new ResetPasswordMail($user, $resetUrl));
		}

		return redirect()->route('admin.login')->with([
			'success' => 'Reset password link has been sent.',
			'buttonText' => 'OK'
		]);
	}

	/**
	 * Show reset password form (Admin).
	 */
	public function showResetForm(Request $request, string $token)
	{
		return view('auth.reset-password', [
			'token' => $token,
			'email' => $request->query('email'),
			'postRoute' => 'admin.password.update',
			'heading' => 'Admin — Reset Password'
		]);
	}

	/**
	 * Handle resetting the password (Admin).
	 */
	public function resetPassword(Request $request)
	{
		$request->validate([
			'token' => ['required'],
			'email' => ['required', 'email'],
			'password' => ['required', 'confirmed', 'min:6'],
		]);

		// Ensure the email belongs to an admin account
		$adminExists = User::where('email', $request->email)
			->whereIn('type', ['admin', 'system-admin'])
			->exists();

		if (!$adminExists) {
			return back()->with([
				'error' => 'Invalid reset request.',
				'buttonText' => 'TRY AGAIN'
			])->withInput($request->only('email'));
		}

		$status = Password::reset(
			$request->only('email', 'password', 'password_confirmation', 'token'),
			function ($user) use ($request) {
				$user->forceFill([
					'password' => Hash::make($request->password),
					'remember_token' => Str::random(60),
				])->save();

				event(new PasswordReset($user));
			}
		);

		if ($status === Password::PASSWORD_RESET) {
			return redirect()->route('admin.login')->with([
				'success' => 'Password has been reset. You may log in.',
				'buttonText' => 'Proceed'
			]);
		}

		return back()->with([
			'error' => __($status),
			'buttonText' => 'TRY AGAIN'
		])->withInput($request->only('email'));
	}
}
