<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\QrToken;
use App\Models\User;
use App\Services\OtpService;
use App\Services\RecaptchaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VoterAuthController extends Controller
{
	public function __construct(private OtpService $otpService, private RecaptchaService $recaptcha) {}

	public function showLogin()
	{
		return view('voter.auth.login');
	}

	public function login(Request $request)
	{
		$request->validate([
			'memberId' => 'required|string',
			'password' => 'required',
			'g-recaptcha-response' => 'nullable|string',
		]);

		// if (!$this->recaptcha->verify((string) $request->input('g-recaptcha-response', ''), 'voter_login')) {
		// 	return back()->with('error', 'reCAPTCHA failed');
		// }

		if (!Auth::attempt(['member_id' => $request->memberId, 'password' => $request->password])) {
			return back()->with([
				'error' => 'Invalid details',
				'buttonText' => 'TRY AGAIN',
			]);
		}


		$request->session()->regenerate();
		session(['otp_verified' => false]);
		// $this->otpService->sendOTP(Auth::user(), 'login');

		$user = $request->user();
		$user->forceFill(['last_signed_in' => now('Asia/Manila')])->save();

		return redirect()->route('voter.otp')->with([
			'success' => 'Valid Details',
			'buttonText' => 'Proceed'
		]);
	}

	public function showOtp()
	{
		return view('voter.auth.otp');
	}

	public function verifyOtp(Request $request)
	{
		$request->validate([
			'code' => 'required|string'
		]);

		$user = Auth::user();

		// if ($user && $this->otpService->verifyOtp($user, $request->code)) {
		if ($user) {
			session(['otp_verified' => true]);
			return redirect()->route('voter.qr')->with([
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
		Auth::logout();
		$request->session()->invalidate();
		$request->session()->regenerateToken();
		return redirect()->route('voter.login');
	}
}
