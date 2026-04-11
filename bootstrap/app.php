<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustHosts(at: ['analyt-loan.onrender.com']);
        $middleware->trustProxies(at: '*');
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'update_last_seen' => \App\Http\Middleware\UpdateUserLastSeen::class,
            'idempotency' => \App\Http\Middleware\FinancialIdempotency::class,
        ]);
        $middleware->append(\App\Http\Middleware\InjectTraceId::class);

        $middleware->web(append: [
            \App\Http\Middleware\DebugSession::class,
            \App\Http\Middleware\EnforceTenancy::class,
            \App\Http\Middleware\OverrideOrganizationTime::class,
            \App\Http\Middleware\CheckOrganizationStatus::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Spatie\Permission\Exceptions\UnauthorizedException|\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException|\Illuminate\Auth\Access\AuthorizationException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized.'], 403);
            }

            return redirect()->back()->with('custom-alert', [
                'type' => 'error',
                'message' => 'ACCESS DENIED: You do not have the required permissions to access this page or perform this action.',
            ]);
        });
    })->create();
