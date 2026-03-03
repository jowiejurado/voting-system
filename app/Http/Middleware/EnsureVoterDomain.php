<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureVoterDomain
{
    /**
     * Ensure the request is on the voter panel domain.
     * Return 404 if accessed from admin or any other domain.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $voterDomain = config('domains.voter');

        if ($request->getHost() !== $voterDomain) {
            abort(404);
        }

        return $next($request);
    }
}
