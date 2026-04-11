<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class DebugSession
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip log routes to avoid recursion
        if ($request->is('authlog*') || $request->is('livewire/update')) {
            return $next($request);
        }

        if ($request->hasSession()) {
            $session = $request->session();

            \App\Models\SessionLog::create([
                'user_id' => Auth::id(),
                'session_id' => $session->getId(),
                'path' => $request->path(),
                'method' => $request->method(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'csrf_token_session' => $session->token(),
                'csrf_token_request' => $request->header('X-CSRF-TOKEN') ?: $request->input('_token'),
                'is_authenticated' => Auth::check(),
                'cookies' => $request->cookies->all(),
                // Filter sensitive fields from payload
                'payload' => collect($request->all())->except(['password', 'password_confirmation', '_token'])->toArray(),
            ]);
        }

        return $next($request);
    }
}
