<?php

namespace Tests\Unit\Services;

use App\Services\CircuitBreaker;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CircuitBreakerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_it_executes_callback_successfully()
    {
        $result = CircuitBreaker::run('test_service', function () {
            return 'success';
        });

        $this->assertEquals('success', $result);
    }

    public function test_it_returns_fallback_on_failure()
    {
        $result = CircuitBreaker::run('test_service', function () {
            throw new \Exception('Failed');
        }, 'fallback');

        $this->assertEquals('fallback', $result);
    }

    public function test_it_forces_failure_in_chaos_mode()
    {
        CircuitBreaker::forceFail('storage', true);

        $result = CircuitBreaker::run('storage', function () {
            return 'real_service';
        }, 'chaos_fallback');

        $this->assertEquals('chaos_fallback', $result);
    }

    public function test_it_opens_circuit_after_threshold()
    {
        // Fail 5 times
        for ($i = 0; $i < 5; $i++) {
            CircuitBreaker::run('bad_service', function () {
                throw new \Exception('Failed');
            }, 'fallback');
        }

        // Now the circuit should be 'open'
        $this->assertEquals('open', Cache::get('circuit_breaker_bad_service'));

        // Next call should immediately return fallback without even trying the callback
        $called = false;
        $result = CircuitBreaker::run('bad_service', function () use (&$called) {
            $called = true;

            return 'should_not_be_called';
        }, 'auto_fallback');

        $this->assertEquals('auto_fallback', $result);
        $this->assertFalse($called);
    }
}
