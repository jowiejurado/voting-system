<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyOtp
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
	 */
	public function handle(Request $request, Closure $next): Response
	{
		if (auth()->check() && session('otp_verified') === true) {
			return $next($request);
		}
		if (auth()->check()) {
			$userType = auth()->user()->type;

			if ($userType == 'admin' || $userType == 'system-admin') {
				return redirect()->route('admin.otp')->with([
					'error' => 'Please verify OTP',
					'buttonText' => 'Proceed'
				]);
			}

			return redirect()->route('voter.otp')->with([
				'error' => 'Please verify OTP',
				'buttonText' => 'Proceed'
			]);
		}
		return redirect('/')->with([
			'error' => 'Please try logging in again',
			'buttonText' => 'Go to log in'
		]);
	}
}
