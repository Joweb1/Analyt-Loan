<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Reports;
use App\Models\Loan;
use App\Models\Organization;
use App\Models\Repayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReportsTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $organization;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->organization = Organization::factory()->create();
        app(\App\Services\TenantSession::class)->setTenantId($this->organization->id);

        // Clear all caches for this org to be absolutely sure
        Reports::clearCache($this->organization->id);

        $this->admin = User::factory()->create(['organization_id' => $this->organization->id]);
    }

    public function test_it_renders_successfully()
    {
        Livewire::actingAs($this->admin)
            ->test(Reports::class)
            ->assertStatus(200);
    }

    public function test_it_switches_report_types()
    {
        Livewire::actingAs($this->admin)
            ->test(Reports::class)
            ->call('setReportType', 'weekly')
            ->assertSet('reportType', 'weekly')
            ->assertDispatched('chartUpdated');
    }

    public function test_it_can_export_loans()
    {
        Loan::factory()->create(['organization_id' => $this->organization->id]);

        Livewire::actingAs($this->admin)
            ->test(Reports::class)
            ->call('exportLoans')
            ->assertStatus(200);
    }

    public function test_it_calculates_correct_summary_stats()
    {
        $now = Organization::systemNow();
        Reports::clearCache($this->organization->id);

        // Create loan disbursed today
        Loan::factory()->create([
            'organization_id' => $this->organization->id,
            'amount' => 50000,
            'release_date' => $now,
            'status' => 'active',
        ]);

        // Create repayment today
        $loan = Loan::factory()->create(['organization_id' => $this->organization->id]);
        Repayment::factory()->create([
            'loan_id' => $loan->id,
            'amount' => 10000,
            'paid_at' => $now,
        ]);

        Livewire::actingAs($this->admin)
            ->test(Reports::class)
            ->assertViewHas('disbursed', 50000)
            ->assertViewHas('collected', 10000);
    }
}
