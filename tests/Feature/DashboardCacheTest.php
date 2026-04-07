<?php

namespace Tests\Feature;

use App\Livewire\AdminDashboard;
use App\Livewire\LoanDashboard;
use App\Models\Borrower;
use App\Models\Loan;
use App\Models\Organization;
use App\Models\Repayment;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardCacheTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $organization;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->organization = Organization::factory()->create();
        app(\App\Services\TenantSession::class)->setTenantId($this->organization->id);
        $this->user = User::factory()->create(['organization_id' => $this->organization->id]);
        $this->user->assignRole('Admin');
    }

    public function test_loan_creation_invalidates_loan_dashboard_cache()
    {
        $orgId = $this->organization->id;

        // 1. Visit dashboard to prime cache
        Livewire::actingAs($this->user)
            ->test(LoanDashboard::class)
            ->assertSet('totalLent', 0);

        $cacheKey = "dashboard_stats_{$orgId}_filter_today";
        $this->assertTrue(Cache::has($cacheKey), 'Cache should be primed for today filter');

        // 2. Create a loan (this should trigger the observer)
        $borrowerUser = User::factory()->create(['organization_id' => $orgId]);
        $borrower = Borrower::factory()->create([
            'organization_id' => $orgId,
            'user_id' => $borrowerUser->id,
        ]);

        Loan::factory()->create([
            'organization_id' => $orgId,
            'borrower_id' => $borrower->id,
            'amount' => 5000,
            'status' => 'active',
            'created_at' => now(),
        ]);

        // 3. Assert cache is cleared
        $this->assertFalse(Cache::has($cacheKey), 'Cache should be invalidated after loan creation');
    }

    public function test_repayment_creation_invalidates_admin_dashboard_cache()
    {
        $orgId = $this->organization->id;

        // 1. Visit admin dashboard to prime cache
        Livewire::actingAs($this->user)
            ->test(AdminDashboard::class)
            ->assertSet('totalCollected', 0);

        $cacheKey = "admin_dashboard_stats_{$orgId}_all";
        $this->assertTrue(Cache::has($cacheKey), 'Admin dashboard cache should be primed');

        // 2. Create a loan and repayment
        $borrowerUser = User::factory()->create(['organization_id' => $orgId]);
        $borrower = Borrower::factory()->create([
            'organization_id' => $orgId,
            'user_id' => $borrowerUser->id,
        ]);

        $loan = Loan::factory()->create([
            'organization_id' => $orgId,
            'borrower_id' => $borrower->id,
            'status' => 'active',
        ]);

        Repayment::factory()->create([
            'loan_id' => $loan->id,
            'amount' => 1000,
            'paid_at' => now(),
        ]);

        // 3. Assert cache is cleared
        $this->assertFalse(Cache::has($cacheKey), 'Admin dashboard cache should be invalidated after repayment');
    }

    public function test_loan_creation_invalidates_reports_cache()
    {
        $orgId = $this->organization->id;

        // 1. Prime reports cache
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Reports::class)
            ->assertSet('reportType', 'daily');

        $cacheKey = "reports_stats_{$orgId}_daily";
        $this->assertTrue(Cache::has($cacheKey), 'Reports cache should be primed');

        // 2. Create a loan
        $borrowerUser = User::factory()->create(['organization_id' => $orgId]);
        $borrower = Borrower::factory()->create([
            'organization_id' => $orgId,
            'user_id' => $borrowerUser->id,
        ]);

        Loan::factory()->create([
            'organization_id' => $orgId,
            'borrower_id' => $borrower->id,
            'amount' => 5000,
            'status' => 'active',
        ]);

        // 3. Assert cache is cleared
        $this->assertFalse(Cache::has($cacheKey), 'Reports cache should be invalidated after loan creation');
    }
}
