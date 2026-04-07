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
        $tenantSession = app(\App\Services\TenantSession::class);

        if ($tenantSession->hasTenant()) {
            $orgId = $tenantSession->getTenantId();
            $org = \App\Models\Organization::where('id', $orgId)->first();

            if ($org) {
                if ($org->use_manual_date && $org->operating_date) {
                    // Get the simulated time from the org model directly to ensure accuracy
                    $dt = $org->getSystemTime();

                    // Set both Carbon test time and PHP default timezone
                    \Carbon\Carbon::setTestNow($dt);

                    if ($org->timezone) {
                        date_default_timezone_set($org->timezone);
                        config(['app.timezone' => $org->timezone]);
                    }
                } else {
                    \Carbon\Carbon::setTestNow();
                }
            }
        }

        return $next($request);
    }
}
