<?php

namespace App\Http\Middleware;

use App\Models\Election;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ActiveElection
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
	 */
	public function handle(Request $request, Closure $next): Response
	{
		$active = Election::where('is_active', true)
			->where(function ($q) {
				$q->whereNull('date')->orWhere('date', '<=', now());
			})
			->where(function ($q) {
				$q->whereNull('start_time')->orWhere('start_time', '>=', now());
			})
			->where(function ($q) {
				$q->whereNull('end_time')->orWhere('end_time', '>=', now());
			})
			->first();

		if (!$active) {
			return redirect()->route('voter.dashboard')->with([
				'error' => 'No active election',
				'buttonText' => 'Okay'
			]);
		}

		$request->attributes->set('activeElection', $active);
		return $next($request);
	}
}
