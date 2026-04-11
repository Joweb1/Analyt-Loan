<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class DebugSession
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only skip the log monitor itself to avoid infinite loops
        if ($request->is('authlog*')) {
            return $next($request);
        }

        if ($request->hasSession()) {
            try {
                $session = $request->session();

                \App\Models\SessionLog::create([
                    'user_id' => Auth::id(),
                    'session_id' => $session->getId(),
                    'path' => $request->fullUrl(),
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
                            'is_https' => $request->secure(),
                            'app_key_set' => ! empty(config('app.key')),
                        ],
                        'request_data' => collect($request->all())->except(['password', 'password_confirmation', '_token'])->toArray(),
                    ],
                ]);
            } catch (\Exception $e) {
                // Silently continue
            }
        }

        $response = $next($request);

        // Force save the session before returning
        if ($request->hasSession()) {
            Session::save();
        }

        return $response;
    }
}
