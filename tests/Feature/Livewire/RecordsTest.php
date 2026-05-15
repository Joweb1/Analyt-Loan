<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Records;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RecordsTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $organization;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->organization = Organization::factory()->create();
        app(\App\Services\TenantSession::class)->setTenantId($this->organization->id);
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->admin = User::factory()->create(['organization_id' => $this->organization->id]);
        $this->admin->assignRole('Admin');
    }

    #[Test]
    public function it_renders_records_hub_successfully()
    {
        Livewire::actingAs($this->admin)
            ->test(Records::class)
            ->assertStatus(200)
            ->assertSee('Records Hub')
            ->assertSee('Select a digital record book')
            ->assertSee('Loan Disbursement')
            ->assertSee('Cash Book')
            ->assertSee('Daily Savings')
            ->assertSee('Repayment/Savings')
            ->assertSee('Savings Withdrawal');
    }

    #[Test]
    public function it_redirects_unauthorized_users()
    {
        $user = User::factory()->create(['organization_id' => $this->organization->id]);
        // No role assigned, will hit the 'role' middleware which redirects (302) if not matched
        // or Laravel's default behavior for role/permission mismatch depending on implementation.
        // In this app, role:Admin,... is used, which redirects if role is not present.

        $this->actingAs($user)
            ->get(route('records'))
            ->assertStatus(302);
    }
}
