<?php
// app/Http/Controllers/VoterFaceController.php

namespace App\Http\Controllers\Voter;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Support\FaceMetric;

class VoterFaceController extends Controller
{
	public function show(Request $request)
	{
		$user = Auth::user();
		abort_unless($user, 403);

		if (!$user->face_descriptor || count($user->face_descriptor) !== 128) {
			return redirect()->route('logout');
		}

		return view('voter.auth.face', [
			'threshold' => 0.6,
			'nextUrl'   => route('voter.ballot'),
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

		if (!$pass) {
			return back()
				->withErrors(['face' => 'Face did not match. Make sure your face is well lit and centered.'])
				->withInput();
		}

		session(['face_verified_at' => now()->toIso8601String()]);

		return redirect()->to($request->input('next', route('voter.ballot')))
			->with([
				'success' => 'Face verification successful.',
				'buttonText' => 'Proceed'
			]);
	}
}
