<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminDomain
{
    /**
     * Ensure the request is on the admin panel domain.
     * Return 404 if accessed from voter or any other domain.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $adminDomain = config('domains.admin');

        if ($request->getHost() !== $adminDomain) {
            abort(404);
        }

        return $next($request);
    }
}
