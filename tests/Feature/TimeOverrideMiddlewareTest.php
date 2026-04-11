<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @group skip
 */
class TimeOverrideMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_middleware_overrides_time_for_authenticated_user()
    {
        $simulatedDate = '2025-01-01';
        $org = Organization::factory()->create([
            'use_manual_date' => true,
            'operating_date' => Carbon::parse($simulatedDate),
            'timezone' => 'UTC',
        ]);

        $user = User::factory()->create([
            'organization_id' => $org->id,
        ]);

        $this->actingAs($user);

        // We'll call a route and check if now() matches the simulated date within the request.
        // Since we can't easily "peek" inside the request in this type of test without a controller,
        // we'll just check if the middleware affected the current process's Carbon after the request.
        // Wait, Laravel tests run in the same process.

        $this->get('/borrowers');

        $this->assertEquals($simulatedDate, now()->toDateString());
    }
}
