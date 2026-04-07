<?php

namespace App\Services;

use App\Support\Tracing;
use Illuminate\Support\Facades\Log;

class ResilienceService
{
    /**
     * Execute a callback with both a circuit breaker and exponential backoff retries.
     */
    public static function execute(string $service, string $op, callable $callback, $fallback = null, int $retries = 3)
    {
        return CircuitBreaker::run($service, function () use ($op, $callback, $retries) {
            return self::retryWithBackoff($op, $callback, $retries);
        }, $fallback);
    }

    /**
     * Execute a callback with exponential backoff retries and tracing.
     */
    public static function retryWithBackoff(string $op, callable $callback, int $times = 3, int $sleepMs = 100)
    {
        $traceId = Tracing::getTraceId();

        return retry($times, function ($attempt) use ($op, $callback, $traceId) {
            $span = Tracing::startSpan("resilience.retry.{$attempt}", "Attempting {$op} (Attempt #{$attempt})");

            try {
                $result = $callback($attempt);
                if ($span) {
                    $span->finish();
                }

                return $result;
            } catch (\Exception $e) {
                if ($span) {
                    $span->setData(['error' => $e->getMessage()]);
                    $span->finish();
                }

                Log::warning("Resilience [{$op}] attempt #{$attempt} failed: ".$e->getMessage(), [
                    'trace_id' => $traceId,
                ]);

                throw $e;
            }
        }, function ($attempt) use ($sleepMs) {
            // Exponential backoff: 100ms, 200ms, 400ms...
            return pow(2, $attempt - 1) * $sleepMs;
        });
    }
}
