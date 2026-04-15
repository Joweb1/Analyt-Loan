<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OverrideOrganizationTime
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->hasSession()) {
            return $next($request);
        }

        // We no longer call Carbon::setTestNow() here.
        // Doing so poisons Laravel's session 'last_activity' timestamp,
        // causing immediate logouts if the organization date is in the past.
        // All business logic has been updated to use Organization::systemNow().

        return $next($request);
    }
}
