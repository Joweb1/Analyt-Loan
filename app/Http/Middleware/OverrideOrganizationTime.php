<?php

namespace App\Http\Middleware;

use App\Models\Organization;
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

        $org = Organization::current();

        if ($org && $org->system_date) {
            // Save the original last_activity to prevent poisoning by setTestNow()
            $originalLastActivity = $request->session()->get('last_activity');

            Carbon::setTestNow($org->getSystemTime());

            try {
                $response = $next($request);
            } finally {
                // CRITICAL: Reset the simulated time BEFORE the response is sent back
                // up the middleware stack. This ensures Laravel's session handler
                // uses REAL-WORLD time to set cookie expiration and session timeouts.
                Carbon::setTestNow();
            }

            // Restore last_activity if it was changed by Laravel's session handler
            // during the request under the simulated time.
            if ($originalLastActivity) {
                $request->session()->put('last_activity', $originalLastActivity);
            }

            return $response;
        }

        return $next($request);
    }
}
