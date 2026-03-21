<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class OverrideOrganizationTime
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && $org = Auth::user()->organization) {
            if ($org->use_manual_date && $org->operating_date) {
                // We set the operating date but keep the current real-time clock portion
                // if they only set the date? Actually, the user said "admin can set current date manually".
                // Let's use the stored timestamp.
                Carbon::setTestNow($org->operating_date);
            }
        }

        return $next($request);
    }
}
