<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSecondFactorComplete
{
	public function handle(Request $request, Closure $next): Response
	{
		if (! Auth::check()) {
			return $next($request);
		}

		if (session('full_login_complete') === true) {
			return $next($request);
		}

		$type = strtolower((string) Auth::user()->type);
		$isVoter = $type === 'voter';
		$route = $isVoter ? 'voter.verify.method' : 'admin.verify.method';

		return redirect()->route($route)->with([
			'error' => 'Complete verification to continue.',
			'buttonText' => 'Proceed',
		]);
	}
}
