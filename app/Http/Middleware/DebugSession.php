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
            try {
                $session = $request->session();

                \App\Models\SessionLog::create([
                    'user_id' => Auth::id(),
                    'session_id' => $session->getId(),
                    'path' => $request->fullUrl(), // Full URL including scheme
                    'method' => $request->method(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'csrf_token_session' => $session->token(),
                    'csrf_token_request' => $request->header('X-CSRF-TOKEN') ?: $request->input('_token'),
                    'is_authenticated' => Auth::check(),
                    'cookies' => $request->cookies->all(),
                    'payload' => [
                        'runtime_config' => [
                            'app_url' => config('app.url'),
                            'session_driver' => config('session.driver'),
                            'session_secure' => config('session.secure'),
                            'session_domain' => config('session.domain'),
                            'session_same_site' => config('session.same_site'),
                            'is_https' => $request->secure(),
                        ],
                        'headers' => [
                            'x-forwarded-proto' => $request->header('X-Forwarded-Proto'),
                            'x-forwarded-host' => $request->header('X-Forwarded-Host'),
                        ],
                    ],
                ]);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('SessionLog failure: '.$e->getMessage());
            }
        }

        return $next($request);
    }
}
