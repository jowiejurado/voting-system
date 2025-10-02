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
	public function __construct(private OtpService $otpService, private RecaptchaService $recaptcha) {}

	public function showLogin()
	{
		return view('admin.auth.login');
	}

	public function login(Request $request)
	{
		$request->validate([
			'adminId' => 'required',
			'password' => 'required',
			'g-recaptcha-response' => 'nullable|string',
		]);

		// if (!$this->recaptcha->verify((string) $request->input('g-recaptcha-response', ''), 'admin_login')) {
		// 	return back()->with([
		// 		'error' => 'reCAPTCHA failed',
		// 		'buttonText' => 'TRY AGAIN'
		// 	]);
		// }

		if (!Auth::attempt(['admin_id' => $request->adminId, 'password' => $request->password])) {
			return back()->with([
				'error' => 'Invalid details',
				'buttonText' => 'TRY AGAIN',
			]);
		}

		$request->session()->regenerate();
		session(['otp_verified' => false]);
		// $this->otpService->generateAndSend(Auth::user(), 'login');

		return redirect()->route('admin.otp')->with([
			'success' => 'Valid Details',
			'buttonText' => 'Proceed'
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

		// if ($user && $this->otpService->verify($user, $request->code, 'login')) {
		if ($user) {
			session(['otp_verified' => true]);
			return redirect()->route('admin.dashboard');
		}

		return back()->with([
			'error' => 'Invalid code',
			'buttonText' => 'TRY AGAIN',
		]);
	}

	public function logout(Request $request)
	{
		Auth::logout();
		$request->session()->invalidate();
		$request->session()->regenerateToken();
		return redirect()->route('admin.login');
	}
}
