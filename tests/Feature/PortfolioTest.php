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
            'status' => 'active',
        ]);

        // Add a repayment
        Repayment::create([
            'loan_id' => $loan->id,
            'amount' => 2000,
            'principal_amount' => 1500,
            'interest_amount' => 500,
            'paid_at' => now(),
        ]);

        // 1. Balance: (10000 + 1000) - 2000 = 9000
        $this->assertEquals(9000, $portfolio->portfolio_balance);

        // 2. Savings Balance
        $account = SavingsAccount::create([
            'organization_id' => $org->id,
            'borrower_id' => $borrower->id,
            'account_number' => 'SAV-TEST',
            'balance' => 5000,
        ]);
        $this->assertEquals(5000, $portfolio->savings_balance);

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

        $this->assertEquals(8500, $portfolio->portfolio_at_risk);
        $this->assertEquals(round((8500 / 9000) * 100, 2), $portfolio->par_percentage);

        // 4. PnL
        $this->assertEquals(500, $portfolio->profit_loss);

        ScheduledRepayment::where('loan_id', $loan->id)->update(['due_date' => now()->subDays(10)]);
        $this->assertEquals(-8000, $portfolio->profit_loss);
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
        $admin = User::factory()->create(['organization_id' => $org->id]);
        $admin->assignRole('Admin');

        $borrower = Borrower::factory()->create(['organization_id' => $org->id]);
        SavingsAccount::create([
            'organization_id' => $org->id,
            'borrower_id' => $borrower->id,
            'account_number' => 'SAV-1',
            'balance' => 1500,
        ]);

        Livewire::actingAs($admin)
            ->test(Reports::class)
            ->assertViewHas('totalSavings', 1500);
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
        Loan::factory()->create([
            'organization_id' => $org->id,
            'portfolio_id' => $portfolio->id,
            'amount' => 5000,
            'status' => 'active',
        ]);

        // Loan outside portfolio
        Loan::factory()->create([
            'organization_id' => $org->id,
            'portfolio_id' => null,
            'amount' => 10000,
            'status' => 'active',
        ]);

        Livewire::actingAs($admin)
            ->test(AdminDashboard::class)
            ->assertSet('totalLoaned', 15000)
            ->set('selectedPortfolioId', $portfolio->id)
            ->assertSet('totalLoaned', 5000);
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
