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
        if (! Auth::check()) {
            return redirect('login');
        }

        $user = Auth::user();

        // If roles are required for this route, and the user does not have any of them
        if (count($roles) > 0 && ! $user->hasAnyRole($roles)) {
            throw \Spatie\Permission\Exceptions\UnauthorizedException::forRoles($roles);
        }

        return $next($request);
    }
}
