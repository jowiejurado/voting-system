<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class VoterOnly
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
	 */
	public function handle(Request $request, Closure $next): Response
	{
		// Not logged in
		if (! Auth::check()) {
			return redirect()->route('auth.login')->with([
				'error' => 'Unauthorized',
				'buttonText' => 'Go back to log in',
			]);
		}

		// Must be voter
		$allowed = ['voter'];
		$type = strtolower((string) Auth::user()->type);

		if (! in_array($type, $allowed, true)) {
			// Optional: make sure they’re not kept logged in
			Auth::logout();

			return redirect()->route('auth.login')->with([
				'error' => 'Unauthorized',
				'buttonText' => 'Go back to log in',
			]);
		}

		$response = $next($request);

		// Prevent back button from showing cached authenticated pages after logout
		$response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
		$response->headers->set('Pragma', 'no-cache');
		$response->headers->set('Expires', '0');

		return $response;
	}
}
