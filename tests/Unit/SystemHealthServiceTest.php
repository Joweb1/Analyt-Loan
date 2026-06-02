<?php

namespace Tests\Unit;

use App\Models\SystemHealthLog;
use App\Services\SystemHealthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SystemHealthServiceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_log_system_events_with_levels()
    {
        SystemHealthService::log('Database', 'success', 'DB is up');

        $this->assertDatabaseHas('system_health_logs', [
            'component' => 'Database',
            'level' => 'success',
            'message' => 'DB is up',
        ]);
    }

    #[Test]
    public function it_can_run_full_system_checks()
    {
        $results = SystemHealthService::check();

        $this->assertArrayHasKey('database', $results);
        $this->assertArrayHasKey('cache', $results);
        $this->assertArrayHasKey('storage', $results);

        // Should have created logs
        $this->assertGreaterThan(0, SystemHealthLog::count());
    }
}
