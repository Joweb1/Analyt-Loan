<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class DebugSession
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $session = $request->session();

        $data = [
            'path' => $request->fullUrl(),
            'method' => $request->method(),
            'session_id' => $session->getId(),
            'csrf_token_session' => $session->token(),
            'csrf_token_request' => $request->header('X-CSRF-TOKEN') ?: $request->input('_token'),
            'is_authenticated' => Auth::check(),
            'user_id' => Auth::id(),
            'cookies' => $request->cookies->all(),
        ];

        Log::channel('stderr')->info('SESSION_DEBUG_LOG', $data);

        $response = $next($request);

        // Log again after the request to see if things changed
        Log::channel('stderr')->info('SESSION_DEBUG_LOG_AFTER', [
            'new_session_id' => $session->getId(),
            'new_csrf_token' => $session->token(),
        ]);

        return $response;
    }
}
