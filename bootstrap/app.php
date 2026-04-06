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
			Route::middleware('web')->group(base_path('routes/web.php'));

			Route::middleware('web')
				->prefix('admin')
				->group(base_path('routes/admin.php'));

			Route::middleware('web')
				->prefix('voter')
				->group(base_path('routes/voter.php'));
		},
	)
	->withMiddleware(function (Middleware $middleware): void {
		$middleware->alias([
			'admin' => \App\Http\Middleware\AdminOnly::class,
			'second.factor' => \App\Http\Middleware\EnsureSecondFactorComplete::class,
			'voter' => \App\Http\Middleware\VoterOnly::class,
			'otp' => \App\Http\Middleware\VerifyOtp::class,
			'active.election' => \App\Http\Middleware\ActiveElection::class,
			'face' => \App\Http\Middleware\FaceVerified::class,
		]);
	})
	->withExceptions(function (Exceptions $exceptions): void {
		$exceptions->render(function (\Throwable $e, Request $request) {
			if ($e instanceof NotFoundHttpException && ! $request->expectsJson()) {
				return redirect()->route('auth.login');
			}
		});
	})->create();
