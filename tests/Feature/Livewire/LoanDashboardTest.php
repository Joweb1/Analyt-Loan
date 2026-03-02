<?php

namespace Tests\Feature\Livewire;

use App\Livewire\LoanDashboard;
use App\Models\Loan;
use App\Models\Organization;
use App\Models\Repayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LoanDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $organization;

    protected User $staff;

    protected function setUp(): void
    {
        parent::setUp();
        $this->organization = Organization::factory()->create();
        $this->staff = User::factory()->create(['organization_id' => $this->organization->id]);
    }

    public function test_it_renders_successfully()
    {
        Livewire::actingAs($this->staff)
            ->test(LoanDashboard::class)
            ->assertStatus(200);
    }

    public function test_it_calculates_pipeline_stats_based_on_filter()
    {
        // Set fixed time to a Wednesday to ensure 'yesterday' is in the same week (Carbon week starts Monday)
        $this->travelTo(now()->next('Wednesday'));

        // Created today
        Loan::factory()->create([
            'organization_id' => $this->organization->id,
            'status' => 'applied',
            'created_at' => now(),
        ]);

        // Created yesterday
        Loan::factory()->create([
            'organization_id' => $this->organization->id,
            'status' => 'applied',
            'created_at' => now()->subDay(),
        ]);

        $component = Livewire::actingAs($this->staff)
            ->test(LoanDashboard::class);

        $component->assertSet('pipelineApplied', 1); // Default filter is today

        $component->set('filter', 'week')
            ->assertSet('pipelineApplied', 2);
    }

    public function test_it_calculates_repayment_stats()
    {
        $loan = Loan::factory()->create(['organization_id' => $this->organization->id]);

        Repayment::factory()->create([
            'loan_id' => $loan->id,
            'amount' => 1000,
            'paid_at' => today(),
        ]);

        Livewire::actingAs($this->staff)
            ->test(LoanDashboard::class)
            ->assertSet('repaidToday', 1000);
    }
}
