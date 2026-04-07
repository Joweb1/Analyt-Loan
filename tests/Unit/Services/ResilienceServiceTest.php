<?php

namespace Tests\Unit\Services;

use App\Services\ResilienceService;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ResilienceServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_it_retries_and_eventually_succeeds()
    {
        $attempts = 0;
        $result = ResilienceService::retryWithBackoff('test_op', function ($attempt) use (&$attempts) {
            $attempts++;
            if ($attempts < 2) {
                throw new \Exception('Failed first attempt');
            }

            return 'success';
        }, 3, 1); // 1ms sleep for tests

        $this->assertEquals(2, $attempts);
        $this->assertEquals('success', $result);
    }

    public function test_it_exhausts_retries_and_throws_exception()
    {
        $attempts = 0;
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Persistent failure');

        ResilienceService::retryWithBackoff('failing_op', function ($attempt) use (&$attempts) {
            $attempts++;
            throw new \Exception('Persistent failure');
        }, 3, 1);

        $this->assertEquals(3, $attempts);
    }

    public function test_it_integrates_with_circuit_breaker()
    {
        // Execute with a service that always fails
        for ($i = 0; $i < 5; $i++) {
            ResilienceService::execute('bad_service', 'bad_op', function () {
                throw new \Exception('Failed');
            }, 'fallback', 1); // Only 1 retry for speed
        }

        // Now the circuit should be 'open'
        $this->assertEquals('open', Cache::get('circuit_breaker_bad_service'));

        // Next call should immediately return fallback
        $result = ResilienceService::execute('bad_service', 'bad_op', function () {
            return 'should_not_run';
        }, 'immediate_fallback');

        $this->assertEquals('immediate_fallback', $result);
    }
}
