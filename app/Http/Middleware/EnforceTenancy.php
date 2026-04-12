<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnforceTenancy
{
    public function __construct(
        protected \App\Services\TenantSession $tenantSession,
        protected \App\Services\Localization $localization
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->hasSession()) {
            return $next($request);
        }

        try {
            if (Auth::check()) {
                $user = Auth::user();

                // Resolve and set the tenant context
                $this->tenantSession->setTenantFromUser();

                // Apply localization
                if ($this->tenantSession->hasTenant()) {
                    $organization = \App\Models\Organization::current(true);
                    if ($organization) {
                        $this->localization->setContext($organization);
                    }
                }

                // Hard enforcement: If not App Owner and no organization is set, deny access.
                if (! $user->isAppOwner() && ! $this->tenantSession->hasTenant()) {
                    $excludedRoutes = [
                        'register.org',
                        'logout',
                        'verification.notice',
                        'verification.verify',
                        'verification.send',
                        'password.confirm',
                        'profile', // Allow profile access to see status
                    ];

                    if (! $request->expectsJson() && $request->route() && ! in_array($request->route()->getName(), $excludedRoutes)) {
                        return redirect()->route('register.org')->with('error', 'You must be part of an organization to access this area.');
                    }

                    if ($request->expectsJson() && ! in_array($request->route()->getName(), $excludedRoutes)) {
                        return response()->json(['error' => 'Tenant context missing.'], 403);
                    }
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Tenancy Error: '.$e->getMessage());
        }

        return $next($request);
    }
}
