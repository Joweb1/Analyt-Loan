<?php

namespace Tests\Feature;

use App\Livewire\Reports;
use App\Models\Borrower;
use App\Models\Loan;
use App\Models\Organization;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReportsDataIntegrityTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $organization;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->organization = Organization::factory()->create();
        app(\App\Services\TenantSession::class)->setTenantId($this->organization->id);
        $this->admin = User::factory()->create(['organization_id' => $this->organization->id]);
        $this->admin->assignRole('Admin');
    }

    public function test_yearly_filter_is_supported()
    {
        Livewire::actingAs($this->admin)
            ->test(Reports::class)
            ->call('setReportType', 'yearly')
            ->assertSet('reportType', 'yearly')
            ->assertDispatched('chartUpdated');
    }

    public function test_total_interest_calculation_integrity()
    {
        $orgId = $this->organization->id;

        // 1. Create a borrower
        $borrower = Borrower::factory()->create(['organization_id' => $orgId]);

        // 2. Create an active loan with 10,000 principal and auto-calculated interest
        $loan = Loan::factory()->create([
            'organization_id' => $orgId,
            'borrower_id' => $borrower->id,
            'amount' => 10000,
            'interest_rate' => 10,
            'interest_type' => 'year',
            'duration' => 12,
            'duration_unit' => 'month',
            'status' => 'active',
        ]);

        $expectedInterest = $loan->getTotalExpectedInterest();

        Livewire::actingAs($this->admin)
            ->test(Reports::class)
            ->assertViewHas('totalInterest', $expectedInterest);
    }

    public function test_interest_from_repaid_loans_is_included_in_lifetime_interest()
    {
        $orgId = $this->organization->id;
        $borrower = Borrower::factory()->create(['organization_id' => $orgId]);

        // Create a FULLY REPAID loan
        $repaidLoan = Loan::factory()->create([
            'organization_id' => $orgId,
            'borrower_id' => $borrower->id,
            'amount' => 5000,
            'interest_rate' => 10,
            'status' => 'repaid',
        ]);

        // Create an ACTIVE loan
        $activeLoan = Loan::factory()->create([
            'organization_id' => $orgId,
            'borrower_id' => $borrower->id,
            'amount' => 10000,
            'interest_rate' => 10,
            'status' => 'active',
        ]);

        $expectedTotal = $repaidLoan->getTotalExpectedInterest() + $activeLoan->getTotalExpectedInterest();

        // Total Interest reflects lifetime total expected interest
        Livewire::actingAs($this->admin)
            ->test(Reports::class)
            ->assertViewHas('totalInterest', $expectedTotal);
    }
}
