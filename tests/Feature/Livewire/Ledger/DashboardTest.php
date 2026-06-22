<?php

namespace Tests\Feature\Livewire\Ledger;

use App\Livewire\Ledger\Dashboard;
use App\Models\Borrower;
use App\Models\Loan;
use App\Models\Organization;
use App\Models\Repayment;
use App\Models\User;
use App\Services\TenantSession;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $organization;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->organization = Organization::factory()->create([
            'system_date' => now()->toDateString(),
        ]);
        app(TenantSession::class)->setTenantId($this->organization->id);
        $this->seed(RoleSeeder::class);
        $this->admin = User::factory()->create(['organization_id' => $this->organization->id]);
        $this->admin->assignRole('Admin');
    }

    #[Test]
    public function it_renders_successfully()
    {
        Livewire::actingAs($this->admin)
            ->test(Dashboard::class)
            ->assertStatus(200);
    }

    #[Test]
    public function it_calculates_weekly_stats_correctly()
    {
        $borrower = Borrower::factory()->create(['organization_id' => $this->organization->id]);
        $loan = Loan::factory()->create([
            'borrower_id' => $borrower->id,
            'organization_id' => $this->organization->id,
            'status' => 'active',
        ]);

        Repayment::factory()->create([
            'loan_id' => $loan->id,
            'borrower_id' => $borrower->id,
            'organization_id' => $this->organization->id,
            'amount' => 50, // 50 Major = 5,000 Minor
            'payment_method' => 'cash',
            'paid_at' => now(),
        ]);

        Livewire::actingAs($this->admin)
            ->test(Dashboard::class)
            ->assertSet('stats.total_repayments_week', function ($val) {
                return $val->getMinorAmount() === 5000;
            })
            ->assertSet('stats.active_weekly_groups', 0);
    }

    #[Test]
    public function it_loads_collection_groups()
    {
        $borrower = Borrower::factory()->create([
            'organization_id' => $this->organization->id,
        ]);
        Loan::factory()->create([
            'borrower_id' => $borrower->id,
            'organization_id' => $this->organization->id,
            'status' => 'active',
            'repayment_cycle' => 'weekly',
            'collection_group' => 'Monday Group',
        ]);

        Livewire::actingAs($this->admin)
            ->test(Dashboard::class)
            ->assertSee('Monday Group')
            ->assertSet('groups.0.members_count', 1);
    }

    #[Test]
    public function it_filters_by_date()
    {
        $lastWeek = now()->subWeek();

        Livewire::actingAs($this->admin)
            ->test(Dashboard::class)
            ->set('selectedDate', $lastWeek->toDateString())
            ->assertSee('Week '.$lastWeek->weekOfMonth);
    }
}
