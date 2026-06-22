<?php

namespace Tests\Feature\Livewire;

use App\Livewire\AdminDashboard;
use App\Models\AccountBalance;
use App\Models\Borrower;
use App\Models\CashbookEntry;
use App\Models\Guarantor;
use App\Models\Loan;
use App\Models\Organization;
use App\Models\Portfolio;
use App\Models\Repayment;
use App\Models\Saver;
use App\Models\ScheduledRepayment;
use App\Models\SystemNotification;
use App\Models\User;
use App\ValueObjects\Money;
use Database\Seeders\RoleSeeder;
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
        $this->seed(RoleSeeder::class);
        $this->admin = User::factory()->create(['organization_id' => $this->organization->id]);
        $this->admin->assignRole('Admin');
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
        $loan1 = Loan::factory()->create([
            'organization_id' => $this->organization->id,
            'borrower_id' => $borrower->id,
            'amount' => 100000.0,
            'interest_rate' => 0,
            'interest_type' => 'year',
            'interest_cycle' => 'year',
            'duration' => 1,
            'duration_unit' => 'year',
            'status' => 'active',
        ]);

        ScheduledRepayment::create([
            'loan_id' => $loan1->id,
            'installment_number' => 1,
            'principal_amount' => 100000.0,
            'interest_amount' => 0,
            'due_date' => now()->addMonth(),
            'status' => 'applied',
        ]);

        // Another loan: 50k
        $loan2 = Loan::factory()->create([
            'organization_id' => $this->organization->id,
            'borrower_id' => $borrower->id,
            'amount' => 50000.0,
            'interest_rate' => 10,
            'interest_type' => 'year',
            'interest_cycle' => 'year',
            'duration' => 1,
            'duration_unit' => 'year',
            'status' => 'active',
        ]);

        ScheduledRepayment::create([
            'loan_id' => $loan2->id,
            'installment_number' => 1,
            'principal_amount' => 50000.0,
            'interest_amount' => 5000.0,
            'due_date' => now()->addMonth(),
            'status' => 'applied',
        ]);

        // Repayment for repaid loan: 55k (Covers 50k principal + 10% interest)
        Repayment::factory()->create([
            'loan_id' => $loan2->id,
            'organization_id' => $this->organization->id,
            'amount' => 55000.0,
            'principal_amount' => 50000.0,
            'interest_amount' => 5000.0,
            'paid_at' => now(),
        ]);

        $loan2->refreshRepaymentStatus();

        // Organisation Total Balance = (100k + 0) + (50k + 5k) - 55k = 100k
        $currency = $this->organization->currency_code ?: 'NGN';
        Livewire::actingAs($this->admin)
            ->test(AdminDashboard::class)
            ->assertSet('totalLoaned', function ($val) {
                // \Log::info('Val type: ' . get_class($val));
                // \Log::info('Val minor: ' . $val->getMinorAmount());
                return $val instanceof Money && $val->getMinorAmount() === 10000000;
            })
            ->assertSet('totalCollected', function ($val) {
                return $val instanceof Money && $val->getMinorAmount() === 5500000;
            })
            ->assertSet('activeLoansCount', 1) // Only Loan 1 is active
            ->assertSet('paidLoansCount', 1); // Loan 2 is repaid
    }

    public function test_it_calculates_customer_breakdown()
    {
        // 1 Borrower
        $userBorrower = User::factory()->create(['organization_id' => $this->organization->id, 'type' => 'customer']);
        $userBorrower->assignRole('Borrower');
        Borrower::factory()->create([
            'user_id' => $userBorrower->id,
            'organization_id' => $this->organization->id,
            'kyc_status' => 'approved',
        ]);

        // Another customer who is also a borrower but kyc pending (Still should count if role is assigned,
        // as we now count by role to match customer list)
        $userPending = User::factory()->create(['organization_id' => $this->organization->id, 'type' => 'customer']);
        $userPending->assignRole('Borrower');
        Borrower::factory()->create([
            'user_id' => $userPending->id,
            'organization_id' => $this->organization->id,
            'kyc_status' => 'pending',
        ]);

        // 1 Saver
        $userSaver = User::factory()->create(['organization_id' => $this->organization->id, 'type' => 'customer']);
        $userSaver->assignRole('Saver');
        $portfolio = Portfolio::create([
            'organization_id' => $this->organization->id,
            'name' => 'Main Portfolio',
            'code' => 'MAIN',
        ]);
        Saver::create([
            'user_id' => $userSaver->id,
            'organization_id' => $this->organization->id,
            'portfolio_id' => $portfolio->id,
            'kyc_status' => 'approved',
        ]);

        // 1 Guarantor
        $userGuarantor = User::factory()->create(['organization_id' => $this->organization->id, 'type' => 'customer']);
        $userGuarantor->assignRole('Guarantor');
        Guarantor::create([
            'user_id' => $userGuarantor->id,
            'organization_id' => $this->organization->id,
            'name' => 'John Doe',
            'phone' => '1234567890',
        ]);

        Livewire::actingAs($this->admin)
            ->test(AdminDashboard::class)
            ->assertSet('borrowersCount', 2)
            ->assertSet('saversCount', 1)
            ->assertSet('guarantorsCount', 1)
            ->assertSee('Borrowers')
            ->assertSee('Savers')
            ->assertSee('Guarantors');
    }

    public function test_it_displays_live_account_balance()
    {
        $date = $this->organization->getSystemTime();
        AdminDashboard::clearCache($this->organization->id);

        AccountBalance::create([
            'organization_id' => $this->organization->id,
            'month' => $date->month,
            'year' => $date->year,
            'opening_balance' => 10000.0, // 1M minor
        ]);

        CashbookEntry::create([
            'organization_id' => $this->organization->id,
            'entry_date' => $date->toDateString(),
            'bank_deposit_amount' => 5000.0,
            'bank_withdrawals' => 2000.0,
            'status' => 'verified',
        ]);

        // Balance should be 10000 + 5000 - 2000 = 13000
        Livewire::actingAs($this->admin)
            ->test(AdminDashboard::class)
            ->assertSet('accountBalance', function ($val) {
                return $val instanceof Money && $val->getMinorAmount() === 1300000;
            })
            ->assertSee('Live Bank Balance')
            ->assertSee('₦ 13,000');
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
