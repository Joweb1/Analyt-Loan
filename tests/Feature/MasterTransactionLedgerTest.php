<?php

namespace Tests\Feature;

use App\Models\Borrower;
use App\Models\Loan;
use App\Models\Organization;
use App\Models\Repayment;
use App\Models\SavingsAccount;
use App\Models\SavingsTransaction;
use App\Models\Transaction;
use App\Models\User;
use App\Services\LoanService;
use App\ValueObjects\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class MasterTransactionLedgerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    /** @test */
    public function it_records_registration_fee_when_saver_is_created()
    {
        $org = Organization::factory()->create(['kyc_status' => 'approved']);
        app(\App\Services\TenantSession::class)->setTenantId($org->id);
        
        $admin = User::factory()->create(['organization_id' => $org->id, 'type' => 'admin']);
        $admin->assignRole('Admin');

        \Livewire\Livewire::actingAs($admin)
            ->test(\App\Livewire\CustomerRegistrationForm::class)
            ->set('organization_id', $org->id)
            ->set('registration_type', 'saver')
            ->set('name', 'John Saver')
            ->set('phone', '08011112222')
            ->set('customData.bank_name', 'Test Bank')
            ->set('customData.account_number', '1234567890')
            ->set('customData.bank_account_name', 'John Saver')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('transactions', [
            'type' => 'registration_fee',
            'amount' => 100000, // 1000 Naira
            'payment_method' => 'cash',
            'organization_id' => $org->id,
        ]);
    }

    /** @test */
    public function it_records_loan_disbursement_transaction()
    {
        $org = Organization::factory()->create();
        app(\App\Services\TenantSession::class)->setTenantId($org->id);
        
        $admin = User::factory()->create(['organization_id' => $org->id, 'type' => 'admin']);
        
        $user = User::factory()->create(['organization_id' => $org->id]);
        $borrower = Borrower::factory()->create(['user_id' => $user->id, 'organization_id' => $org->id]);
        
        $loan = Loan::factory()->create([
            'organization_id' => $org->id,
            'borrower_id' => $borrower->id,
            'amount' => 5000,
            'status' => 'approved'
        ]);

        Auth::login($admin);
        app(LoanService::class)->activateLoan($loan);

        $this->assertDatabaseHas('transactions', [
            'type' => 'loan_disbursement',
            'amount' => 500000,
            'user_id' => $user->id,
            'organization_id' => $org->id,
        ]);
    }

    /** @test */
    public function it_records_repayment_transaction_via_observer()
    {
        $org = Organization::factory()->create();
        app(\App\Services\TenantSession::class)->setTenantId($org->id);
        
        $admin = User::factory()->create(['organization_id' => $org->id, 'type' => 'admin']);
        
        $user = User::factory()->create(['organization_id' => $org->id]);
        $borrower = Borrower::factory()->create(['user_id' => $user->id, 'organization_id' => $org->id]);
        
        $loan = Loan::factory()->create([
            'organization_id' => $org->id,
            'borrower_id' => $borrower->id,
            'amount' => 5000,
            'status' => 'active'
        ]);

        Auth::login($admin);
        
        Repayment::create([
            'loan_id' => $loan->id,
            'organization_id' => $org->id,
            'amount' => 1000,
            'payment_method' => 'cash',
            'paid_at' => now(),
            'collected_by' => $admin->id,
        ]);

        $this->assertDatabaseHas('transactions', [
            'type' => 'repayment',
            'amount' => 100000,
            'user_id' => $user->id,
            'organization_id' => $org->id,
        ]);
    }

    /** @test */
    public function it_records_savings_deposit_transaction_via_observer()
    {
        $org = Organization::factory()->create();
        app(\App\Services\TenantSession::class)->setTenantId($org->id);
        
        $admin = User::factory()->create(['organization_id' => $org->id, 'type' => 'admin']);
        
        $user = User::factory()->create(['organization_id' => $org->id]);
        $account = SavingsAccount::factory()->create([
            'user_id' => $user->id,
            'organization_id' => $org->id
        ]);

        Auth::login($admin);

        SavingsTransaction::create([
            'savings_account_id' => $account->id,
            'type' => 'deposit',
            'amount' => 2000,
            'payment_method' => 'bank_transfer',
            'transaction_date' => now(),
            'staff_id' => $admin->id,
            'reference' => 'DEP-123'
        ]);

        $this->assertDatabaseHas('transactions', [
            'type' => 'deposit',
            'amount' => 200000,
            'payment_method' => 'bank_transfer',
            'user_id' => $user->id,
            'organization_id' => $org->id,
        ]);
    }

    /** @test */
    public function it_does_not_duplicate_transactions_for_extra_repayment_savings()
    {
        $org = Organization::factory()->create();
        app(\App\Services\TenantSession::class)->setTenantId($org->id);
        
        $admin = User::factory()->create(['organization_id' => $org->id, 'type' => 'admin']);
        
        $user = User::factory()->create(['organization_id' => $org->id]);
        $borrower = Borrower::factory()->create(['user_id' => $user->id, 'organization_id' => $org->id]);
        $account = SavingsAccount::factory()->create(['user_id' => $user->id, 'organization_id' => $org->id]);
        
        $loan = Loan::factory()->create([
            'organization_id' => $org->id,
            'borrower_id' => $borrower->id,
            'amount' => 5000,
            'status' => 'active'
        ]);

        Auth::login($admin);

        // Repayment with extra amount
        $repayment = Repayment::create([
            'loan_id' => $loan->id,
            'organization_id' => $org->id,
            'amount' => 1200, // 1000 for loan, 200 extra
            'extra_amount' => 200,
            'payment_method' => 'cash',
            'paid_at' => now(),
            'collected_by' => $admin->id,
        ]);

        // Link simulation
        SavingsTransaction::create([
            'savings_account_id' => $account->id,
            'repayment_id' => $repayment->id,
            'type' => 'deposit',
            'amount' => 200,
            'payment_method' => 'cash',
            'transaction_date' => now(),
            'staff_id' => $admin->id,
            'reference' => 'REP-EXTRA'
        ]);

        // Total transactions should be 1 (the repayment itself)
        $this->assertEquals(1, Transaction::where('user_id', $user->id)->count());
        $this->assertDatabaseHas('transactions', [
            'type' => 'repayment',
            'amount' => 120000,
            'organization_id' => $org->id,
        ]);
    }
}
