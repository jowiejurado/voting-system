<?php

namespace App\Http\Controllers\Voter;

use App\Http\Controllers\Controller;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
	public function __construct(private OtpService $otp) {}

	public function show()
	{
		return view('voter.account');
	}

	public function sendOtp(Request $request)
	{
		$this->otp->sendOTP(Auth::user());
		return back()->with('success', 'Code sent');
	}

	public function updatePassword(Request $request)
	{
		$request->validate([
			'current_password' => 'required',
			'new_password' => 'required|min:6',
			'code' => 'required',
		]);
		$user = Auth::user();
		if (!Hash::check($request->current_password, $user->password)) {
			return back()->with('error', 'Current password incorrect');
		}
		if (!$this->otp->verify($user, $request->code, 'change_password')) {
			return back()->with('error', 'Invalid code');
		}
		$user->password = Hash::make($request->new_password);
		$user->save();
		return back()->with('success', 'Password updated');
	}
}
