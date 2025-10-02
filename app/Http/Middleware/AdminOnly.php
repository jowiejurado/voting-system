<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminOnly
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
			return redirect()->route('admin.login')->with([
				'error' => 'Unauthorized',
				'buttonText' => 'Go back to log in',
			]);
		}

		// Must be admin or system-admin
		$allowed = ['admin', 'system-admin'];
		$type = strtolower((string) Auth::user()->type);

		if (! in_array($type, $allowed, true)) {
			// Optional: make sure they’re not kept logged in
			Auth::logout();

			return redirect()->route('admin.login')->with([
				'error' => 'Unauthorized',
				'buttonText' => 'Go back to log in',
			]);
		}

		return $next($request);
	}
}
