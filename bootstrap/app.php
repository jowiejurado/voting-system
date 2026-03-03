<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
	->withRouting(
		commands: __DIR__ . '/../routes/console.php',
		health: '/up',
		then: function () {
			Route::domain(config('domains.admin'))
				->middleware(['web', 'ensure.admin.domain'])
				->group(base_path('routes/admin.php'));

			Route::domain(config('domains.voter'))
				->middleware(['web', 'ensure.voter.domain'])
				->group(base_path('routes/voter.php'));
		},
	)
	->withMiddleware(function (Middleware $middleware): void {
		$middleware->alias([
			'admin' => \App\Http\Middleware\AdminOnly::class,
			'voter' => \App\Http\Middleware\VoterOnly::class,
			'otp' => \App\Http\Middleware\VerifyOtp::class,
			'active.election' => \App\Http\Middleware\ActiveElection::class,
			'face' => \App\Http\Middleware\FaceVerified::class,
			'ensure.admin.domain' => \App\Http\Middleware\EnsureAdminDomain::class,
			'ensure.voter.domain' => \App\Http\Middleware\EnsureVoterDomain::class,
		]);
	})
	->withExceptions(function (Exceptions $exceptions): void {
		$exceptions->render(function (\Throwable $e, Request $request) {
			if ($e instanceof NotFoundHttpException && ! $request->expectsJson()) {
				$host = $request->getHost();
				if ($host === config('domains.admin')) {
					return response()->view('errors.404-admin', [], 404);
				}
				if ($host === config('domains.voter')) {
					return response()->view('errors.404-voter', [], 404);
				}
			}
		});
	})->create();
