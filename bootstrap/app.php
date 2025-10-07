<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // All middleware
        $middleware->alias([
            'auth'             => \App\Http\Middleware\Authenticate::class,
            'auth.basic'       => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
            'auth.session'     => \Illuminate\Session\Middleware\AuthenticateSession::class,
            'cache.headers'    => \Illuminate\Http\Middleware\SetCacheHeaders::class,
            'can'              => \Illuminate\Auth\Middleware\Authorize::class,
            'guest'            => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
            'signed'           => \App\Http\Middleware\ValidateSignature::class,
            'throttle'         => \Illuminate\Routing\Middleware\ThrottleRequests::class,
            'verified'         => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
            'checkUser'        => \App\Http\Middleware\Checkuser::class,
            'adminuser'        => \App\Http\Middleware\Adminuser::class,
            'role'              => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission'        => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission'=> \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Render custom 403 page for authorization failures in web requests
        $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'User does not have the right permissions.',
                    'code' => 403,
                ], 403);
            }
            return response()->view('errors.403', [], 403);
        });

        // Fallback for any other 403 HTTP exceptions
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, \Illuminate\Http\Request $request) {
            if ($e->getStatusCode() !== 403) {
                return null; // let default handler process
            }
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'User does not have the right permissions.',
                    'code' => 403,
                ], 403);
            }
            return response()->view('errors.403', [], 403);
        });
    })->create();
