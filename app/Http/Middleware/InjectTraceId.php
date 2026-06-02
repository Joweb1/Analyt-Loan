<?php

namespace App\Http\Middleware;

use App\Support\Tracing;
use Closure;
use Illuminate\Http\Request;
use Sentry\State\HubInterface;
use Sentry\State\Scope;
use Symfony\Component\HttpFoundation\Response;

class InjectTraceId
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $traceId = $request->header('X-Trace-Id', Tracing::getTraceId());
        Tracing::setTraceId($traceId);

        if (app()->bound(HubInterface::class)) {
            app(HubInterface::class)->configureScope(function (Scope $scope) use ($traceId) {
                $scope->setTag('trace_id', $traceId);
            });
        }

        $response = $next($request);

        $response->headers->set('X-Trace-Id', $traceId);

        return $response;
    }
}
