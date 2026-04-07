<?php

namespace Tests\Feature;

use App\Events\DashboardUpdated;
use App\Livewire\AdminDashboard;
use App\Livewire\LoanDashboard;
use App\Models\Borrower;
use App\Models\Loan;
use App\Models\Organization;
use App\Models\Repayment;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class RealtimeDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $organization;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->organization = Organization::factory()->create();
        $this->user = User::factory()->create(['organization_id' => $this->organization->id]);
        $this->user->assignRole('Admin');
    }

    public function test_loan_creation_broadcasts_dashboard_update()
    {
        Event::fake([DashboardUpdated::class]);

        $borrowerUser = User::factory()->create(['organization_id' => $this->organization->id]);
        $borrower = Borrower::factory()->create([
            'organization_id' => $this->organization->id,
            'user_id' => $borrowerUser->id,
        ]);

        Loan::factory()->create([
            'organization_id' => $this->organization->id,
            'borrower_id' => $borrower->id,
        ]);

        Event::assertDispatched(DashboardUpdated::class, function ($event) {
            return $event->organizationId === $this->organization->id;
        });
    }

    public function test_repayment_creation_broadcasts_dashboard_update()
    {
        Event::fake([DashboardUpdated::class]);

        $borrowerUser = User::factory()->create(['organization_id' => $this->organization->id]);
        $borrower = Borrower::factory()->create([
            'organization_id' => $this->organization->id,
            'user_id' => $borrowerUser->id,
        ]);

        $loan = Loan::factory()->create([
            'organization_id' => $this->organization->id,
            'borrower_id' => $borrower->id,
            'status' => 'active',
        ]);

        Repayment::factory()->create([
            'loan_id' => $loan->id,
            'amount' => 1000,
        ]);

        Event::assertDispatched(DashboardUpdated::class, function ($event) {
            return $event->organizationId === $this->organization->id;
        });
    }

    public function test_dashboards_have_correct_broadcasting_listeners()
    {
        $orgId = $this->organization->id;

        $loanDashboard = new LoanDashboard;
        $this->actingAs($this->user);
        $listeners = $loanDashboard->getListeners();
        $this->assertArrayHasKey("echo:organization.{$orgId},.dashboard.updated", $listeners);

        $adminDashboard = new AdminDashboard;
        $listenersAdmin = $adminDashboard->getListeners();
        $this->assertArrayHasKey("echo:organization.{$orgId},.dashboard.updated", $listenersAdmin);
    }

    public function test_loan_update_broadcasts_dashboard_update()
    {
        Event::fake([DashboardUpdated::class]);

        $borrowerUser = User::factory()->create(['organization_id' => $this->organization->id]);
        $borrower = Borrower::factory()->create([
            'organization_id' => $this->organization->id,
            'user_id' => $borrowerUser->id,
        ]);

        $loan = Loan::factory()->create([
            'organization_id' => $this->organization->id,
            'borrower_id' => $borrower->id,
            'status' => 'applied',
        ]);

        $loan->update(['status' => 'approved']);

        Event::assertDispatched(DashboardUpdated::class);
    }
}
