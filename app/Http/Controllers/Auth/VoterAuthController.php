<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\UserSecurityQuestion;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class VoterAuthController extends Controller
{
	public function __construct(private OtpService $otpService) {}

	public function showLogin()
	{
		return view('voter.auth.login');
	}

	public function login(Request $request)
	{
		$request->validate([
			'memberId' => ['required'],
			'password' => ['required'],
			'g-recaptcha-response' => ['required', 'captcha'],
		], [
			'g-recaptcha-response.required' => 'Please confirm you are not a robot.',
			'g-recaptcha-response.captcha'  => 'reCAPTCHA verification failed. Please try again.',
		]);

		if (!Auth::attempt(['member_id' => $request->memberId, 'password' => $request->password])) {
			return back()->with([
				'error' => 'Invalid details',
				'buttonText' => 'TRY AGAIN',
			]);
		}

		$request->session()->regenerate();
		session(['otp_verified' => false]);
		$this->otpService->sendOTP(Auth::user(), 'login');

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
		$request->validate(['code' => 'required|string']);
		$user = Auth::user();

		if ($user && $this->otpService->verifyOtp($user, $request->code)) {
		// if ($user) {
			session(['otp_verified' => true]);
			return redirect()->route('voter.verify.method')->with([
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

	public function sendOtp(Request $request)
	{
		$user = Auth::user();

		if (!$user) {
			return response()->json([
				'success' => false,
				'message' => 'Failed to send OTP',
				'buttonText' => 'TRY AGAIN'
			], 500);
		}

		$this->otpService->sendOTP($user);

		return response()->json([
			'success' => true,
			'message' => 'OTP has been sent',
			'buttonText' => 'Proceed'
		], 201);
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

		if (!$this->otpService->verifyOtp($user, $request->otp, 'change_password')) {
			return back()->with([
				'error' => 'Invalid Code',
				'buttonText' => 'TRY AGAIN',
				'__action' => 'change-password'
			]);
		}

		$user->forceFill(['password' => Hash::make($request->password)])->save();

		return redirect()->route('voter.ballot')->with([
			'success' => 'Password updated',
			'buttonText' => 'Proceed'
		]);
	}

	public function showVerifyMethod(Request $request)
	{
		if (!session('otp_verified')) {
			return redirect()->route('voter.otp');
		}

		return view('voter.auth.verify-method');
	}

	public function showSecurityQuestion(Request $request)
	{
		if (!session('otp_verified')) {
			return redirect()->route('voter.otp');
		}

		$user = Auth::user();
		$question = $user->securityQuestions()->inRandomOrder()->first();

		if (!$question) {
			return redirect()->route('voter.verify.method')->with([
				'error' => 'No security questions found for your account. Please use Face Verification.',
				'buttonText' => 'Proceed'
			]);
		}

		session(['sq_question_id' => $question->id]);

		return view('voter.auth.security', ['question' => $question]);
	}

	public function verifySecurityAnswer(Request $request)
	{
		if (!session('otp_verified')) {
			return redirect()->route('voter.otp');
		}

		$request->validate([
			'answer' => 'required|string|min:2|max:255'
		], [
			'answer.min' => 'Answer must be at least 2 characters.',
		]);

		$qid = session('sq_question_id');
		$q = $qid ? UserSecurityQuestion::find($qid) : null;

		if (!$q || $q->user_id !== Auth::id()) {
			return back()->with([
				'error' => 'Session expired. Please try again.',
				'buttonText' => 'TRY AGAIN'
			]);
		}

		$normalized = UserSecurityQuestion::normalizeAnswer($request->answer);
		if (\Illuminate\Support\Facades\Hash::check($normalized, $q->answer_hash)) {
			return redirect()->route('voter.ballot')->with([
				'success' => 'Security verification successful.',
				'buttonText' => 'Proceed'
			]);
		}

		return back()->with([
			'error' => 'Incorrect answer. Please try again.',
			'buttonText' => 'TRY AGAIN'
		]);
	}
}
