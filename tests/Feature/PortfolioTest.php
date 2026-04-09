<?php

namespace Tests\Feature;

use App\Livewire\AdminDashboard;
use App\Livewire\BorrowerList;
use App\Livewire\Components\OmnibarSearch;
use App\Livewire\Reports;
use App\Livewire\Settings\Portfolios;
use App\Models\Borrower;
use App\Models\Loan;
use App\Models\Organization;
use App\Models\Portfolio;
use App\Models\Repayment;
use App\Models\SavingsAccount;
use App\Models\ScheduledRepayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PortfolioTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);

        // We will set the tenant dynamically in each test for this file
        // since it uses Organization::factory()->create() inside them.
    }

    #[Test]
    public function it_calculates_correct_portfolio_metrics()
    {
        $org = Organization::factory()->create();
        $portfolio = Portfolio::create([
            'organization_id' => $org->id,
            'name' => 'Test Portfolio',
        ]);

        $borrower = Borrower::factory()->create([
            'organization_id' => $org->id,
            'portfolio_id' => $portfolio->id,
        ]);

        // Create a loan
        $loan = Loan::factory()->create([
            'organization_id' => $org->id,
            'portfolio_id' => $portfolio->id,
            'borrower_id' => $borrower->id,
            'amount' => 10000,
            'interest_rate' => 10,
            'interest_type' => 'month',
            'duration' => 1,
            'duration_unit' => 'month',
            'status' => 'active',
        ]);

        // Manually create scheduled repayments to represent Principal + Interest (Total 11,000)
        ScheduledRepayment::create([
            'loan_id' => $loan->id,
            'installment_number' => 1,
            'principal_amount' => 5000,
            'interest_amount' => 500,
            'due_date' => now()->addDays(30),
            'status' => 'applied',
        ]);
        ScheduledRepayment::create([
            'loan_id' => $loan->id,
            'installment_number' => 2,
            'principal_amount' => 5000,
            'interest_amount' => 500,
            'due_date' => now()->addDays(60),
            'status' => 'applied',
        ]);

        // Add a repayment
        Repayment::create([
            'loan_id' => $loan->id,
            'amount' => 2000,
            'principal_amount' => 1500,
            'interest_amount' => 500,
            'paid_at' => now(),
        ]);

        // 1. Balance: (1,000,000 principal + 100,000 interest) - 200,000 paid = 900,000
        $this->assertEquals(900000, $portfolio->portfolio_balance->getMinorAmount());

        // 2. Savings Balance
        $account = SavingsAccount::create([
            'organization_id' => $org->id,
            'borrower_id' => $borrower->id,
            'account_number' => 'SAV-TEST',
            'balance' => 5000, // 5,000 Major = 500,000 Minor
        ]);
        $this->assertEquals(500000, $portfolio->savings_balance->getMinorAmount());

        // 3. PAR (Portfolio at Risk)
        ScheduledRepayment::create([
            'loan_id' => $loan->id,
            'installment_number' => 1,
            'amount' => 1000,
            'principal_amount' => 1000,
            'interest_amount' => 0,
            'due_date' => now()->subDays(5),
            'status' => 'overdue',
        ]);

        $this->assertEquals(850000, $portfolio->portfolio_at_risk->getMinorAmount());
        // Balance is 11,000 (initial) - 2,000 (paid) = 9,000
        $this->assertEquals(94.44, $portfolio->par_percentage);

        // 4. PnL
        $this->assertEquals(50000, $portfolio->profit_loss->getMinorAmount());

        ScheduledRepayment::where('loan_id', $loan->id)->update(['due_date' => now()->subDays(10)]);
        $this->assertEquals(-800000, $portfolio->profit_loss->getMinorAmount());
    }

    #[Test]
    public function staff_can_only_see_borrowers_in_assigned_portfolios()
    {
        $org = Organization::factory()->create();
        $portfolioA = Portfolio::create(['organization_id' => $org->id, 'name' => 'Portfolio A']);
        $portfolioB = Portfolio::create(['organization_id' => $org->id, 'name' => 'Portfolio B']);

        $staff = User::factory()->create(['organization_id' => $org->id]);
        $staff->assignRole('Loan Analyst');
        $staff->portfolios()->attach($portfolioA->id);

        $borrowerA = Borrower::factory()->create(['organization_id' => $org->id, 'portfolio_id' => $portfolioA->id]);
        $borrowerB = Borrower::factory()->create(['organization_id' => $org->id, 'portfolio_id' => $portfolioB->id]);

        $this->actingAs($staff);

        $visibleBorrowers = Borrower::all();
        $this->assertTrue($visibleBorrowers->contains($borrowerA));
        $this->assertFalse($visibleBorrowers->contains($borrowerB));
    }

    #[Test]
    public function admin_can_see_all_borrowers_regardless_of_portfolio()
    {
        $org = Organization::factory()->create();
        $portfolioA = Portfolio::create(['organization_id' => $org->id, 'name' => 'Portfolio A']);

        $admin = User::factory()->create(['organization_id' => $org->id]);
        $admin->assignRole('Admin');

        $borrowerA = Borrower::factory()->create(['organization_id' => $org->id, 'portfolio_id' => $portfolioA->id]);
        $borrowerUnassigned = Borrower::factory()->create(['organization_id' => $org->id, 'portfolio_id' => null]);

        $this->actingAs($admin);

        $visibleBorrowers = Borrower::all();
        $this->assertTrue($visibleBorrowers->contains($borrowerA));
        $this->assertTrue($visibleBorrowers->contains($borrowerUnassigned));
    }

    #[Test]
    public function borrower_list_filters_by_portfolio()
    {
        $org = Organization::factory()->create();
        $portfolio = Portfolio::create(['organization_id' => $org->id, 'name' => 'Specific']);

        $admin = User::factory()->create(['organization_id' => $org->id]);
        $admin->assignRole('Admin');

        Borrower::factory()->create(['organization_id' => $org->id, 'portfolio_id' => $portfolio->id]);
        Borrower::factory()->create(['organization_id' => $org->id, 'portfolio_id' => null]);

        Livewire::actingAs($admin)
            ->test(BorrowerList::class)
            ->set('portfolioId', $portfolio->id)
            ->assertViewHas('borrowers', function ($borrowers) use ($portfolio) {
                return $borrowers->count() === 1 && $borrowers->first()->portfolio_id === $portfolio->id;
            });
    }

    #[Test]
    public function reports_page_shows_org_wide_metrics()
    {
        $org = Organization::factory()->create();
        app(\App\Services\TenantSession::class)->setTenantId($org->id);

        $admin = User::factory()->create(['organization_id' => $org->id]);
        $admin->assignRole('Admin');

        $borrower = Borrower::factory()->create(['organization_id' => $org->id]);
        $account = \App\Models\SavingsAccount::create([
            'organization_id' => $org->id,
            'borrower_id' => $borrower->id,
            'account_number' => 'SAV-1',
            'balance' => 0,
        ]);

        \App\Models\SavingsTransaction::create([
            'savings_account_id' => $account->id,
            'amount' => 15, // 15 Major = 1500 Minor
            'type' => 'deposit',
            'staff_id' => $admin->id,
            'transaction_date' => now(),
        ]);

        Livewire::actingAs($admin)
            ->test(Reports::class)
            ->assertViewHas('totalSavings', 15.0);
    }

    #[Test]
    public function admin_can_create_portfolio_via_livewire()
    {
        $org = Organization::factory()->create();
        $admin = User::factory()->create(['organization_id' => $org->id]);
        $admin->assignRole('Admin');

        Livewire::actingAs($admin)
            ->test(Portfolios::class)
            ->set('name', 'Investment Portfolio')
            ->set('description', 'High yield loans')
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('custom-alert', ['type' => 'success', 'message' => 'Portfolio created successfully.']);

        $this->assertDatabaseHas('portfolios', [
            'name' => 'Investment Portfolio',
            'organization_id' => $org->id,
        ]);
    }

    #[Test]
    public function admin_can_assign_staff_and_borrowers_to_portfolio()
    {
        $org = Organization::factory()->create();
        $admin = User::factory()->create(['organization_id' => $org->id]);
        $admin->assignRole('Admin');

        $portfolio = Portfolio::create(['organization_id' => $org->id, 'name' => 'Target Portfolio']);
        $staff = User::factory()->create(['organization_id' => $org->id]);
        $staff->assignRole('Loan Analyst');

        $borrower = Borrower::factory()->create(['organization_id' => $org->id]); // Unassigned

        Livewire::actingAs($admin)
            ->test(Portfolios::class)
            ->set('portfolioId', $portfolio->id)
            ->set('name', 'Updated Name')
            ->set('staffIds', [$staff->id])
            ->set('selectedBorrowerIds', [$borrower->id])
            ->call('save');

        $this->assertTrue($portfolio->staff->contains($staff));
        $this->assertEquals($portfolio->id, $borrower->fresh()->portfolio_id);
    }

    #[Test]
    public function portfolios_are_searchable_without_prefix()
    {
        $org = Organization::factory()->create();
        $admin = User::factory()->create(['organization_id' => $org->id]);
        $admin->assignRole('Admin');

        Portfolio::create(['organization_id' => $org->id, 'name' => 'Special Group']);

        Livewire::actingAs($admin)
            ->test(OmnibarSearch::class)
            ->set('query', 'Special')
            ->assertViewHas('results', function ($results) {
                return collect($results)->where('title', 'Special Group')->count() > 0;
            });
    }

    #[Test]
    public function dashboard_filters_metrics_by_portfolio()
    {
        $org = Organization::factory()->create();
        $admin = User::factory()->create(['organization_id' => $org->id]);
        $admin->assignRole('Admin');

        $portfolio = Portfolio::create(['organization_id' => $org->id, 'name' => 'Filtered']);

        // Loan in portfolio
        $loan1 = Loan::factory()->create([
            'organization_id' => $org->id,
            'portfolio_id' => $portfolio->id,
            'amount' => 5000,
            'interest_rate' => 0,
            'status' => 'active',
        ]);

        ScheduledRepayment::create([
            'loan_id' => $loan1->id,
            'principal_amount' => 5000,
            'interest_amount' => 0,
            'due_date' => now()->addMonth(),
            'installment_number' => 1,
            'status' => 'applied',
        ]);

        // Loan outside portfolio
        $loan2 = Loan::factory()->create([
            'organization_id' => $org->id,
            'portfolio_id' => null,
            'amount' => 10000,
            'interest_rate' => 0,
            'status' => 'active',
        ]);

        ScheduledRepayment::create([
            'loan_id' => $loan2->id,
            'principal_amount' => 10000,
            'interest_amount' => 0,
            'due_date' => now()->addMonth(),
            'installment_number' => 1,
            'status' => 'applied',
        ]);

        Livewire::actingAs($admin)
            ->test(AdminDashboard::class)
            ->assertSet('totalLoaned', function ($val) {
                return $val instanceof \App\ValueObjects\Money && $val->getMinorAmount() === 1500000;
            })
            ->set('selectedPortfolioId', $portfolio->id)
            ->assertSet('totalLoaned', function ($val) {
                return $val instanceof \App\ValueObjects\Money && $val->getMinorAmount() === 500000;
            });
    }

    #[Test]
    public function moving_borrower_syncs_loans_to_new_portfolio()
    {
        $org = Organization::factory()->create();
        $admin = User::factory()->create(['organization_id' => $org->id]);
        $admin->assignRole('Admin');

        $portfolio = Portfolio::create(['organization_id' => $org->id, 'name' => 'New Home']);
        $borrower = Borrower::factory()->create(['organization_id' => $org->id]);
        $loan = Loan::factory()->create(['borrower_id' => $borrower->id, 'organization_id' => $org->id]);

        Livewire::actingAs($admin)
            ->test(Portfolios::class)
            ->set('portfolioId', $portfolio->id)
            ->set('name', 'New Home')
            ->set('selectedBorrowerIds', [$borrower->id])
            ->call('save');

        $this->assertEquals($portfolio->id, $loan->fresh()->portfolio_id);
    }
}
