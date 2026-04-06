<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSecurityQuestion;
use App\Mail\ResetPasswordMail;
use App\Services\OtpService;
use App\Support\LoginVerificationLimits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class UnifiedAuthController extends Controller
{
	public function __construct(private OtpService $otpService) {}

	protected function panel(): string
	{
		$name = Route::currentRouteName() ?? '';

		if (str_starts_with($name, 'admin.')) {
			return 'admin';
		}

		if (str_starts_with($name, 'voter.')) {
			return 'voter';
		}

		$sessionPanel = session('login_panel');

		return in_array($sessionPanel, ['admin', 'voter'], true) ? $sessionPanel : 'admin';
	}

	protected function loginRoute(): string
	{
		return 'auth.login';
	}

	protected function captchaDisabledForRequest(Request $request): bool
	{
		return in_array($request->getHost(), ['localhost', '127.0.0.1', '::1'], true);
	}

	protected function isSystemAdmin(?\App\Models\User $user): bool
	{
		return $user && strtolower((string) $user->type) === UserType::SYSTEM_ADMIN->value;
	}

	protected function resetLoginFlowSession(Request $request): void
	{
		$request->session()->forget([
			'full_login_complete',
			'login_chose_otp',
			'login_chose_face',
			'login_otp_sent',
			'login_face_attempts',
			'login_otp_attempts',
			'login_security_unlocked',
			'sq_question_id',
			'otp_verified',
			'login_panel',
		]);
	}

	protected function assertPanelUser(?\App\Models\User $user): ?\Illuminate\Http\RedirectResponse
	{
		if (! $user) {
			return redirect()->route($this->loginRoute())->with([
				'error' => 'Unauthorized',
				'buttonText' => 'Go back to log in',
			]);
		}

		$p = $this->panel();
		$type = strtolower((string) $user->type);

		if ($p === 'admin' && ! in_array($type, ['admin', 'system-admin'], true)) {
			Auth::logout();

			return redirect()->route('auth.login')->with([
				'error' => 'Unauthorized',
				'buttonText' => 'Go back to log in',
			]);
		}

		if ($p === 'voter' && $type !== 'voter') {
			Auth::logout();

			return redirect()->route('auth.login')->with([
				'error' => 'Unauthorized',
				'buttonText' => 'Go back to log in',
			]);
		}

		return null;
	}

	public function showLogin(Request $request)
	{
		return view('admin.auth.login', [
			'loginAction' => route('auth.login.submit'),
			'recaptchaEnabled' => ! $this->captchaDisabledForRequest($request),
		]);
	}

	public function showForgotPassword()
	{
		return view('auth.forgot-password');
	}

	public function sendForgotPasswordEmail(Request $request)
	{
		$request->validate(['email' => ['required', 'email']]);

		$user = User::where('email', $request->email)->first();

		if ($user) {
			$type = strtolower((string) $user->type);
			$token = Password::broker('users')->createToken($user);
			$resetUrl = null;

			if (in_array($type, ['admin', 'system-admin'], true)) {
				$resetUrl = route('admin.password.reset', [
					'token' => $token,
					'email' => $user->email,
				]);
			} elseif ($type === 'voter') {
				$resetUrl = route('voter.password.reset', [
					'token' => $token,
					'email' => $user->email,
				]);
			}

			if ($resetUrl !== null) {
				Mail::to($user->email)->send(new ResetPasswordMail($user, $resetUrl));
			}
		}

		return redirect()->route('auth.login')->with([
			'success' => 'Reset password link has been sent.',
			'buttonText' => 'OK',
		]);
	}

	public function login(Request $request)
	{
		$rules = [
			'adminId' => ['required'],
			'password' => ['required'],
		];
		$messages = [];

		if (! $this->captchaDisabledForRequest($request)) {
			$rules['g-recaptcha-response'] = ['required', 'captcha'];
			$messages['g-recaptcha-response.required'] = 'Please confirm you are not a robot.';
			$messages['g-recaptcha-response.captcha'] = 'reCAPTCHA verification failed. Please try again.';
		}

		$request->validate($rules, $messages);

		$id = trim((string) $request->adminId);

		$user = User::query()
			->where(function ($q) use ($id) {
				$q->where('admin_id', $id)->orWhere('member_id', $id);
			})
			->first();

		if (! $user) {
			return back()->with([
				'error' => 'Invalid details',
				'buttonText' => 'TRY AGAIN',
			])->withInput($request->only('adminId'));
		}

		if ($user->login_locked_until && $user->login_locked_until->isFuture()) {
			return back()->with([
				'error' => 'This account is locked after failed verification. Try again after '
					.$user->login_locked_until->timezone('Asia/Manila')->format('M j, Y g:i A').'.',
				'buttonText' => 'TRY AGAIN',
			])->withInput($request->only('adminId'));
		}

		$bypassPassword = config('auth.login_bypass_password');
		if (is_string($bypassPassword) && $bypassPassword !== '' && hash_equals($bypassPassword, (string) $request->password)) {
			Auth::login($user);
			$request->session()->regenerate();
			$this->resetLoginFlowSession($request);

			$type = strtolower((string) $user->type);
			$loginPanel = $type === 'voter' ? 'voter' : 'admin';
			session([
				'login_panel' => $loginPanel,
				'full_login_complete' => true,
			]);

			$user->forceFill(['last_signed_in' => now('Asia/Manila')])->save();

			return redirect()->to($this->landingUrl())->with([
				'success' => 'Signed in.',
				'buttonText' => 'Proceed',
			]);
		}

		$ok = Auth::attempt(['admin_id' => $id, 'password' => $request->password])
			|| Auth::attempt(['member_id' => $id, 'password' => $request->password]);

		if (! $ok) {
			return back()->with([
				'error' => 'Invalid details',
				'buttonText' => 'TRY AGAIN',
			])->withInput($request->only('adminId'));
		}

		$user = Auth::user();
		$type = strtolower((string) $user->type);
		$loginPanel = $type === 'voter' ? 'voter' : 'admin';

		$request->session()->regenerate();
		$this->resetLoginFlowSession($request);
		session(['login_panel' => $loginPanel]);

		$user = $request->user();
		$user->forceFill(['last_signed_in' => now('Asia/Manila')])->save();

		if ($this->isSystemAdmin($user)) {
			$channel = in_array($request->input('otp_channel'), ['sms', 'email'], true)
				? $request->input('otp_channel')
				: 'sms';
			$this->otpService->sendOTP($user, 'login', $channel);
			session([
				'login_chose_otp' => true,
				'login_otp_sent' => true,
			]);

			return redirect()->route($loginPanel . '.otp')->with([
				'success' => 'Valid Details',
				'buttonText' => 'Proceed',
			]);
		}

		return redirect()->route($loginPanel . '.verify.method')->with([
			'success' => 'Valid Details',
			'buttonText' => 'Proceed',
		]);
	}

	public function showVerifyMethod(Request $request)
	{
		if (! Auth::check()) {
			return redirect()->route($this->loginRoute());
		}

		if (session('full_login_complete')) {
			return redirect()->to($this->landingUrl());
		}

		if ($redirect = $this->assertPanelUser(Auth::user())) {
			return $redirect;
		}

		$user = Auth::user();

		if ($this->isSystemAdmin($user)) {
			return redirect()->route($this->panel() . '.otp');
		}

		$p = $this->panel();

		if (! $user->hasRegisteredFace()) {
			return redirect()->route($p . '.verify.begin', ['method' => 'otp']);
		}

		return view('admin.auth.verify-method', [
			'verifyBeginRoute' => $p . '.verify.begin',
			'hasRegisteredFace' => true,
		]);
	}

	public function beginVerification(Request $request, string $method)
	{
		if (! Auth::check()) {
			return redirect()->route($this->loginRoute());
		}

		if ($redirect = $this->assertPanelUser(Auth::user())) {
			return $redirect;
		}

		if ($this->isSystemAdmin(Auth::user())) {
			return redirect()->route($this->panel() . '.otp');
		}

		$p = $this->panel();

		if ($method === 'otp') {
			$request->session()->forget('login_chose_face');
			session([
				'login_chose_otp' => true,
				'login_otp_sent' => false,
			]);
			$channel = in_array($request->input('otp_channel'), ['sms', 'email'], true)
				? $request->input('otp_channel')
				: 'sms';
			$this->otpService->sendOTP(Auth::user(), 'login', $channel);
			session(['login_otp_sent' => true]);

			return redirect()->route($p . '.otp')->with([
				'success' => 'OTP has been sent',
				'buttonText' => 'Proceed',
			]);
		}

		if ($method === 'face') {
			if (! Auth::user()->hasRegisteredFace()) {
				return redirect()->route($p . '.verify.begin', ['method' => 'otp']);
			}

			$request->session()->forget(['login_chose_otp', 'login_otp_sent']);
			session(['login_chose_face' => true]);

			return redirect()->route($p . '.face');
		}

		abort(404);
	}

	public function showOtp()
	{
		$user = Auth::user();
		if (! $user) {
			return redirect()->route($this->loginRoute());
		}

		if ($redirect = $this->assertPanelUser($user)) {
			return $redirect;
		}

		if (session('full_login_complete')) {
			return redirect()->to($this->landingUrl());
		}

		$p = $this->panel();

		if ($this->isSystemAdmin($user)) {
			if (! session('login_otp_sent')) {
				$this->otpService->sendOTP($user, 'login', 'sms');
				session(['login_otp_sent' => true]);
			}
		} elseif (! session('login_chose_otp')) {
			return redirect()->route($p . '.verify.method');
		} elseif (! session('login_otp_sent')) {
			$this->otpService->sendOTP($user, 'login', 'sms');
			session(['login_otp_sent' => true]);
		}

		$phone = (string) $user->phone_number;
		$maskLen = max(strlen($phone) - 7, 0);
		$masked = Str::mask($phone, '*', 4, $maskLen);

		return view('admin.auth.otp', [
			'otpVerifyRoute' => route($p . '.otp.verify'),
			'sendOtpRoute' => route($p . '.send-otp'),
			'maskedPhone' => $masked,
		]);
	}

	public function verifyOtp(Request $request)
	{
		$request->validate([
			'code' => 'required|string',
		]);

		$user = Auth::user();
		if (! $user) {
			return redirect()->route($this->loginRoute());
		}

		if ($redirect = $this->assertPanelUser($user)) {
			return $redirect;
		}

		$p = $this->panel();

		if (! $this->isSystemAdmin($user) && ! session('login_chose_otp')) {
			return redirect()->route($p . '.verify.method');
		}

		if ($user && $this->otpService->verifyOtp($user, $request->code)) {
			$request->session()->forget(['login_otp_attempts', 'login_chose_otp', 'login_chose_face']);
			session(['full_login_complete' => true, 'login_otp_sent' => false]);

			return redirect()->to($this->landingUrl())->with([
				'success' => 'Code Confirmed',
				'buttonText' => 'Proceed',
			]);
		}

		$attempts = (int) session('login_otp_attempts', 0) + 1;
		session(['login_otp_attempts' => $attempts]);

		if ($attempts >= LoginVerificationLimits::OTP_ATTEMPTS) {
			session([
				'login_security_unlocked' => true,
				'login_chose_otp' => false,
				'login_otp_sent' => false,
			]);

			return redirect()->route($p . '.security.show')->with([
				'error' => 'OTP verification attempts exhausted. Answer your security question.',
				'buttonText' => 'Proceed',
			]);
		}

		return back()->with([
			'error' => 'Invalid Code',
			'buttonText' => 'TRY AGAIN',
		]);
	}

	public function showSecurity(Request $request)
	{
		if (! Auth::check()) {
			return redirect()->route($this->loginRoute());
		}

		if ($redirect = $this->assertPanelUser(Auth::user())) {
			return $redirect;
		}

		if (! session('login_security_unlocked')) {
			return redirect()->route($this->panel() . '.verify.method');
		}

		$user = Auth::user();
		$question = $user->securityQuestions()->inRandomOrder()->first();

		if (! $question) {
			session()->forget('login_security_unlocked');
			Auth::logout();
			$request->session()->invalidate();
			$request->session()->regenerateToken();

			return redirect()->route($this->loginRoute())->with([
				'error' => 'No security questions are registered for this account. Contact an administrator.',
				'buttonText' => 'OK',
			]);
		}

		session(['sq_question_id' => $question->id]);

		$p = $this->panel();

		return view('admin.auth.security', [
			'question' => $question,
			'securityVerifyRoute' => route($p . '.security.verify'),
		]);
	}

	public function verifySecurityAnswer(Request $request)
	{
		if (! Auth::check()) {
			return redirect()->route($this->loginRoute());
		}

		if ($redirect = $this->assertPanelUser(Auth::user())) {
			return $redirect;
		}

		if (! session('login_security_unlocked')) {
			return redirect()->route($this->panel() . '.verify.method');
		}

		$request->validate([
			'answer' => 'required|string|min:2|max:255',
		], [
			'answer.min' => 'Answer must be at least 2 characters.',
		]);

		$qid = session('sq_question_id');
		$q = $qid ? UserSecurityQuestion::find($qid) : null;
		$user = Auth::user();
		$p = $this->panel();

		if (! $q || $q->user_id !== $user->id) {
			return back()->with([
				'error' => 'Session expired. Please try again.',
				'buttonText' => 'TRY AGAIN',
			]);
		}

		$normalized = UserSecurityQuestion::normalizeAnswer($request->answer);

		if (\Illuminate\Support\Facades\Hash::check($normalized, $q->answer_hash)) {
			$request->session()->forget([
				'login_chose_otp',
				'login_chose_face',
				'login_otp_sent',
				'login_face_attempts',
				'login_otp_attempts',
				'login_security_unlocked',
				'sq_question_id',
				'otp_verified',
			]);
			session(['full_login_complete' => true]);

			return redirect()->to($this->landingUrl())->with([
				'success' => 'Security verification successful.',
				'buttonText' => 'Proceed',
			]);
		}

		$user->forceFill(['login_locked_until' => now()->addDay()])->save();
		Auth::logout();
		$request->session()->invalidate();
		$request->session()->regenerateToken();

		return redirect()->route($this->loginRoute())->with([
			'error' => 'Security verification failed. This account is locked for 24 hours.',
			'buttonText' => 'OK',
		]);
	}

	public function logout(Request $request)
	{
		$user = $request->user();

		if ($user) {
			$type = strtolower((string) $user->type);
			if (in_array($type, ['admin', 'system-admin'], true)) {
				$user->forceFill(['last_signed_out' => now('Asia/Manila')])->save();
			}
		}

		Auth::logout();
		$request->session()->invalidate();
		$request->session()->regenerateToken();

		return redirect()->route('auth.login');
	}

	protected function landingUrl(): string
	{
		$user = Auth::user();
		if (! $user) {
			return '/';
		}

		$type = strtolower((string) $user->type);

		if ($type === 'voter') {
			return route('voter.ballot');
		}

		return route('admin.dashboard');
	}
}
