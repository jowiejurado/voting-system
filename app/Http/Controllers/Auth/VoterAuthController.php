<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;

class VoterAuthController extends Controller
{
	public function __construct(private OtpService $otpService) {}

	public function sendOtp(Request $request)
	{
		$user = Auth::user();

		if (! $user) {
			if ($request->expectsJson()) {
				return response()->json([
					'success' => false,
					'message' => 'Failed to send OTP',
					'buttonText' => 'TRY AGAIN',
				], 500);
			}

			return back()->with([
				'error' => 'Failed to send OTP',
				'buttonText' => 'TRY AGAIN',
			]);
		}

		$channel = in_array($request->input('channel'), ['sms', 'email'], true) ? $request->input('channel') : 'sms';
		$context = $request->input('context', 'change-password');
		$this->otpService->sendOTP($user, $context, $channel);

		if ($request->expectsJson()) {
			return response()->json([
				'success' => true,
				'message' => 'OTP has been sent',
				'buttonText' => 'Proceed',
			], 201);
		}

		return back()->with([
			'success' => 'OTP has been sent',
			'buttonText' => 'Proceed',
		]);
	}

	public function changePassword(Request $request)
	{
		$request->validate([
			'current_password' => 'required',
			'password' => 'required',
			'otp' => 'required|digits:6',
		]);

		$user = $request->user();

		if (! Hash::check($request->current_password, $user->password)) {
			return back()->with([
				'error' => 'Invalid Details',
				'buttonText' => 'TRY AGAIN',
				'__action' => 'change-password',
			]);
		}

		if (! $this->otpService->verifyOtp($user, $request->otp, 'change_password')) {
			return back()->with([
				'error' => 'Invalid Code',
				'buttonText' => 'TRY AGAIN',
				'__action' => 'change-password',
			]);
		}

		$user->forceFill(['password' => Hash::make($request->password)])->save();

		return redirect()->route('voter.ballot')->with([
			'success' => 'Password updated',
			'buttonText' => 'Proceed',
		]);
	}

	public function showResetForm(Request $request, string $token)
	{
		return view('auth.reset-password', [
			'token' => $token,
			'email' => $request->query('email'),
			'postRoute' => 'voter.password.update',
			'heading' => 'Voter — Reset Password',
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

		if (! $voterExists) {
			return back()->with([
				'error' => 'Invalid reset request.',
				'buttonText' => 'TRY AGAIN',
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
			return redirect()->route('auth.login')->with([
				'success' => 'Password has been reset. You may log in.',
				'buttonText' => 'Proceed',
			]);
		}

		return back()->with([
			'error' => __($status),
			'buttonText' => 'TRY AGAIN',
		])->withInput($request->only('email'));
	}
}
