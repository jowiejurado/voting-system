<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FaceVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && session('face_verified') === true) {
            return $next($request);
        }
        return redirect()->route('voter.face')->with('error', 'Please complete face verification');
    }
}
