<?php

namespace Tests\Feature\Livewire;

use App\Livewire\AdminDashboard;
use App\Models\Borrower;
use App\Models\Loan;
use App\Models\Organization;
use App\Models\Repayment;
use App\Models\SystemNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $organization;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->organization = Organization::factory()->create();
        $this->admin = User::factory()->create(['organization_id' => $this->organization->id]);
    }

    public function test_it_renders_successfully()
    {
        Livewire::actingAs($this->admin)
            ->test(AdminDashboard::class)
            ->assertStatus(200);
    }

    public function test_it_calculates_correct_summary_metrics()
    {
        $borrower = Borrower::factory()->create(['organization_id' => $this->organization->id]);

        // Active loan: 100k
        Loan::factory()->create([
            'organization_id' => $this->organization->id,
            'borrower_id' => $borrower->id,
            'amount' => 100000,
            'status' => 'active',
        ]);

        // Repaid loan: 50k
        $loan2 = Loan::factory()->create([
            'organization_id' => $this->organization->id,
            'borrower_id' => $borrower->id,
            'amount' => 50000,
            'interest_rate' => 10,
            'status' => 'active',
        ]);

        // Repayment for repaid loan: 55k (Covers 50k principal + 10% interest)
        Repayment::factory()->create([
            'loan_id' => $loan2->id,
            'amount' => 55000,
            'paid_at' => now(),
        ]);

        Livewire::actingAs($this->admin)
            ->test(AdminDashboard::class)
            ->assertSet('totalLoaned', 150000)
            ->assertSet('totalCollected', 55000)
            ->assertSet('activeLoansCount', 1)
            ->assertSet('paidLoansCount', 1);
    }

    public function test_it_loads_action_items()
    {
        SystemNotification::create([
            'organization_id' => $this->organization->id,
            'title' => 'Urgent Action',
            'message' => 'Action required',
            'type' => 'info',
            'is_actionable' => true,
            'priority' => 'high',
        ]);

        Livewire::actingAs($this->admin)
            ->test(AdminDashboard::class)
            ->assertSet('actionItems', function ($items) {
                // Should at least contain our urgent action
                return collect($items)->contains('title', 'Urgent Action');
            });
    }
}
