<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OtpService;
use App\Services\RecaptchaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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
			return redirect()->route('admin.dashboard')->with([
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

		$this->otpService->sendOTP($user);

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

		if (!$this->otpService->verifyOtp($user, $request->otp)) {
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
}
