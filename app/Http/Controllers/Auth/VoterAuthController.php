<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\UserSecurityQuestion;
use App\Services\OtpService;
use App\Mail\ResetPasswordMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;

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

	public function showForgotPassword()
	{
		return view('voter.auth.forgot-password');
	}

	public function sendResetLinkEmail(Request $request)
	{
		$request->validate(['email' => ['required', 'email']]);

		$user = \App\Models\User::where('email', $request->email)
			->where('type', 'voter')
			->first();

		if ($user) {
			$token = Password::broker('users')->createToken($user);
			$resetUrl = route('voter.password.reset', [
				'token' => $token,
				'email' => $user->email,
			]);
			Mail::to($user->email)->send(new ResetPasswordMail($user, $resetUrl));
		}

		return redirect()->route('voter.login')->with([
			'success' => 'Reset password link has been sent.',
			'buttonText' => 'OK'
		]);
	}

	public function showResetForm(Request $request, string $token)
	{
		return view('auth.reset-password', [
			'token' => $token,
			'email' => $request->query('email'),
			'postRoute' => 'voter.password.update',
			'heading' => 'Voter — Reset Password'
		]);
	}

	public function resetPassword(Request $request)
	{
		$request->validate([
			'token' => ['required'],
			'email' => ['required', 'email'],
			'password' => ['required', 'confirmed', 'min:6'],
		]);

		$voterExists = \App\Models\User::where('email', $request->email)
			->where('type', 'voter')
			->exists();

		if (!$voterExists) {
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
			return redirect()->route('voter.login')->with([
				'success' => 'Password has been reset. You may log in.',
				'buttonText' => 'Proceed'
			]);
		}

		return back()->with([
			'error' => __($status),
			'buttonText' => 'TRY AGAIN'
		])->withInput($request->only('email'));
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
