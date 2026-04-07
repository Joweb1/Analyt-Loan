<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CircuitBreaker
{
    /**
     * Execute a callback with a circuit breaker.
     */
    public static function run(string $service, callable $callback, $fallback = null)
    {
        // Chaos Testing: Force failure if set in cache or environment
        if (self::isForcedFail($service)) {
            self::reportFailure($service);

            return is_callable($fallback) ? $fallback() : $fallback;
        }

        $key = "circuit_breaker_{$service}";
        $status = Cache::get($key, 'closed'); // closed, open, half-open

        if ($status === 'open') {
            return is_callable($fallback) ? $fallback() : $fallback;
        }

        try {
            $result = $callback();

            // If it was half-open and succeeded, we can close it
            if ($status === 'half-open') {
                Cache::forget($key);
                Cache::forget("{$key}_failures");
            }

            return $result;
        } catch (\Exception $e) {
            self::reportFailure($service);
            Log::error("Circuit Breaker [{$service}] triggered: ".$e->getMessage());

            return is_callable($fallback) ? $fallback() : $fallback;
        }
    }

    /**
     * Report a failure for a service.
     */
    public static function reportFailure(string $service, int $threshold = 5, int $timeout = 60): void
    {
        $failuresKey = "circuit_breaker_{$service}_failures";
        $statusKey = "circuit_breaker_{$service}";

        $failures = Cache::increment($failuresKey);

        if ($failures >= $threshold) {
            Cache::put($statusKey, 'open', $timeout);
        }
    }

    /**
     * Chaos Engineering: Force a service to fail for testing.
     */
    public static function forceFail(string $service, bool $status = true): void
    {
        Cache::put("chaos_fail_{$service}", $status, 60);
    }

    /**
     * Check if a service is in "forced failure" mode.
     */
    private static function isForcedFail(string $service): bool
    {
        return Cache::get("chaos_fail_{$service}", false);
    }
}
