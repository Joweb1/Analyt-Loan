<?php

namespace App\Http\Middleware;

use App\Models\ProcessedRequest;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class FinancialIdempotency
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only enforce for state-changing operations
        if (! $request->isMethod('POST') && ! $request->isMethod('PUT') && ! $request->isMethod('PATCH')) {
            return $next($request);
        }

        $key = $request->header('X-Idempotency-Key');

        if (! $key) {
            return $next($request);
        }

        $userId = Auth::id();

        // Check if this request has already been processed
        $existing = ProcessedRequest::where('idempotency_key', $key)
            ->where('user_id', $userId)
            ->first();

        if ($existing) {
            return response()->json($existing->response_body, $existing->status_code)
                ->header('X-Idempotency-Status', 'REPLAYED');
        }

        $response = $next($request);

        // Store the result if it was successful (2xx) or a client error (4xx)
        // We don't store 5xx because those are retriable.
        if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 500) {
            // We only store the body if it's JSON or small text
            $body = $response->getContent();
            $decodedBody = json_decode($body, true) ?: ['content' => $body];

            ProcessedRequest::create([
                'idempotency_key' => $key,
                'user_id' => $userId,
                'status_code' => $response->getStatusCode(),
                'response_body' => $decodedBody,
            ]);
        }

        return $response;
    }
}
