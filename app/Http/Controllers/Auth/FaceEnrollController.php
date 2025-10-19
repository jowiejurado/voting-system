<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FaceEnrollController extends Controller
{
	public function show()
	{
		return view('face.enroll');
	}

	public function store(Request $request)
	{
		$data = $request->validate([
			'descriptor' => ['required', 'array', 'size:128'],
			'descriptor.*' => ['numeric'],
		]);

		$user = Auth::user();
		$user->face_descriptor = json_encode(array_map('floatval', $data['descriptor']));
		$user->face_enrolled_at = now();
		$user->save();

		return response()->json(['message' => 'Face enrolled.']);
	}
}
