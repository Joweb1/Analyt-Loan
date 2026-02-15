<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckOrganizationStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): \Symfony\Component\HttpFoundation\Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Skip check for App Owner
            if ($user->isAppOwner()) {
                return $next($request);
            }

            $organization = $user->organization;

            if ($organization) {
                if ($organization->status === 'suspended') {
                    Auth::logout();
                    return redirect()->route('login')->with('error', 'Your organization account has been suspended. Please contact support.');
                }
            }
        }

        return $next($request);
    }
}
