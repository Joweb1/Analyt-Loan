<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('skip')]
class TimeOverrideMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_middleware_overrides_time_for_authenticated_user()
    {
        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);

        $simulatedDate = '2025-01-01';
        $org = Organization::factory()->create([
            'system_date' => $simulatedDate,
            'timezone' => 'UTC',
        ]);

        $user = User::factory()->create([
            'organization_id' => $org->id,
        ]);
        $user->assignRole('Admin');

        $this->actingAs($user);

        // We'll call a route and check if the response content contains the simulated date.
        // The layout renders an indicator when simulation is active.
        $response = $this->get(route('customer'));
        $response->assertStatus(200);

        $response->assertSee(Carbon::parse($simulatedDate)->format('M d, Y'));

        // Outside the request, time should be reset to real-world time
        $this->assertNotEquals($simulatedDate, now()->toDateString());
    }
}
