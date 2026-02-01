<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect('login');
        }

        $user = Auth::user();

        // If roles are required for this route, and the user does not have any of them
        if (count($roles) > 0 && !$user->hasAnyRole($roles)) {
            // If the user is a borrower, send them to the borrower dashboard.
            if ($user->hasRole('Borrower')) {
                return redirect()->route('borrower.dashboard');
            }
            
            // For any other user without the required role, send to the main dashboard.
            // If they are already on the dashboard, this will cause a loop.
            // So we only redirect if they are NOT on the dashboard route.
            if ($request->route()->getName() !== 'dashboard') {
                return redirect()->route('dashboard');
            }

            // If we are here, it means the user is on the dashboard, but does not have the
            // required roles. And they are not a borrower.
            // This is the case for a user with no roles.
            // We can log them out, or show an error.
            // For now, let's just abort with a 403.
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}