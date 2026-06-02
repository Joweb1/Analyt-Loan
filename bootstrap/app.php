<?php

use App\Http\Middleware\CheckOrganizationStatus;
use App\Http\Middleware\DebugSession;
use App\Http\Middleware\EnforceTenancy;
use App\Http\Middleware\FinancialIdempotency;
use App\Http\Middleware\InjectTraceId;
use App\Http\Middleware\OverrideOrganizationTime;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\UpdateUserLastSeen;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

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
        $middleware->trustHosts(at: [
            'analyt-loan.onrender.com',
            'analytloan.com',
        ]);
        $middleware->trustProxies(at: '*');

        $middleware->encryptCookies(except: [
            // No exceptions for standard cookies to avoid collisions
        ]);

        $middleware->validateCsrfTokens(except: [
            'login',
            'livewire/*',
        ]);

        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'update_last_seen' => UpdateUserLastSeen::class,
            'idempotency' => FinancialIdempotency::class,
        ]);
        $middleware->append(InjectTraceId::class);

        $middleware->web(append: [
            DebugSession::class,
            EnforceTenancy::class,
            OverrideOrganizationTime::class,
            CheckOrganizationStatus::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (UnauthorizedException|AccessDeniedHttpException|AuthorizationException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized.'], 403);
            }

            return redirect()->back()->with('custom-alert', [
                'type' => 'error',
                'message' => 'ACCESS DENIED: You do not have the required permissions to access this page or perform this action.',
            ]);
        });
    })->create();
