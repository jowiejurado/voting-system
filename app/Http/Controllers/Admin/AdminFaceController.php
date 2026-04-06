<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Support\FaceMetric;
use App\Support\LoginVerificationLimits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminFaceController extends Controller
{
	public function show(Request $request)
	{
		$user = Auth::user();
		abort_unless($user, 403);

		$isSystem = strtolower((string) $user->type) === UserType::SYSTEM_ADMIN->value;
		if (! $isSystem && ! session('login_chose_face')) {
			return redirect()->route('admin.verify.method');
		}

		if (! $user->face_descriptor || count($user->face_descriptor) !== 128) {
			return redirect()->route('admin.logout')->with([
				'error' => 'Admin face is not yet registered. Please contact your system admin.',
				'buttonText' => 'Okay',
			]);
		}

		return view('admin.auth.face', [
			'threshold' => 0.6,
			'nextUrl' => route('admin.dashboard'),
			'faceVerifyRoute' => route('admin.face.verify'),
		]);
	}

	public function verify(Request $request)
	{
		$data = $request->validate([
			'face_descriptor_json' => 'required|string',
		]);

		$user = Auth::user();
		abort_unless($user, 403);

		try {
			$live = json_decode($data['face_descriptor_json'], true, 512, JSON_THROW_ON_ERROR);
		} catch (\Throwable $e) {
			return back()->withErrors(['face' => 'Invalid face data. Please try again.']);
		}

		if (!is_array($live) || count($live) !== 128) {
			return back()->withErrors(['face' => 'Face not detected clearly. Please try again.']);
		}

		$live = array_map('floatval', $live);
		$saved = array_map('floatval', $user->face_descriptor ?? []);

		if (count($saved) !== 128) {
			return back()->withErrors(['face' => 'No face on file. Please contact support.']);
		}

		$distance = FaceMetric::euclidean($live, $saved);
		$cosine   = FaceMetric::cosine($live, $saved);
		$threshold = 0.6;

		$pass = $distance <= $threshold;

		if (! $pass) {
			$attempts = (int) session('login_face_attempts', 0) + 1;
			session(['login_face_attempts' => $attempts]);

			if ($attempts >= LoginVerificationLimits::FACE_ATTEMPTS) {
				session([
					'login_security_unlocked' => true,
					'login_chose_face' => false,
				]);

				return redirect()->route('admin.security.show')->with([
					'error' => 'Face verification attempts exhausted. Answer your security question.',
					'buttonText' => 'Proceed',
				]);
			}

			return back()
				->withErrors(['face' => 'Face did not match. Make sure your face is well lit and centered.'])
				->withInput();
		}

		$request->session()->forget(['login_face_attempts', 'login_chose_face', 'login_chose_otp']);
		session([
			'full_login_complete' => true,
			'face_verified_at' => now()->toIso8601String(),
		]);

		return redirect()->to($request->input('next', route('admin.dashboard')))
			->with([
				'success' => 'Face verification successful.',
				'buttonText' => 'Proceed'
			]);
	}
}
