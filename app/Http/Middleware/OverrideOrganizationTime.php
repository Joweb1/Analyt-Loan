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

        try {
            $tenantSession = app(\App\Services\TenantSession::class);

            if ($tenantSession->hasTenant()) {
                $orgId = $tenantSession->getTenantId();
                $org = \App\Models\Organization::where('id', $orgId)->first();

                if ($org && $org->use_manual_date && $org->operating_date) {
                    // Set Carbon test time globally for this request
                    // We specifically DO NOT call date_default_timezone_set here
                    // to avoid shifting the cookie expiration headers.
                    \Carbon\Carbon::setTestNow($org->getSystemTime());
                } else {
                    \Carbon\Carbon::setTestNow();
                }
            }
        } catch (\Exception $e) {
            // Log error but don't crash the request or logout the user
            \Illuminate\Support\Facades\Log::error('TimeOverride Error: '.$e->getMessage());
        }

        return $next($request);
    }
}
