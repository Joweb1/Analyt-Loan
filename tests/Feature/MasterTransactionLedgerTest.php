<?php

namespace Tests\Feature;

use App\Livewire\CustomerRegistrationForm;
use App\Models\Borrower;
use App\Models\Loan;
use App\Models\Organization;
use App\Models\Repayment;
use App\Models\SavingsAccount;
use App\Models\SavingsTransaction;
use App\Models\Transaction;
use App\Models\User;
use App\Services\LoanService;
use App\Services\TenantSession;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MasterTransactionLedgerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    #[Test]
    public function it_records_registration_fee_only_for_borrowers()
    {
        $org = Organization::factory()->create(['kyc_status' => 'approved']);
        app(TenantSession::class)->setTenantId($org->id);

        $admin = User::factory()->create(['organization_id' => $org->id, 'type' => 'admin']);
        $admin->assignRole('Admin');

        // 1. Test Saver (Should NOT record fee)
        Livewire::actingAs($admin)
            ->test(CustomerRegistrationForm::class)
            ->set('organization_id', $org->id)
            ->set('registration_type', 'saver')
            ->set('name', 'John Saver')
            ->set('phone', '08011112222')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('transactions', [
            'type' => 'registration_fee',
            'organization_id' => $org->id,
        ]);

        // 2. Test Borrower (Should record fee)
        Livewire::actingAs($admin)
            ->test(CustomerRegistrationForm::class)
            ->set('organization_id', $org->id)
            ->set('registration_type', 'borrower')
            ->set('name', 'John Borrower')
            ->set('phone', '08033334444')
            ->set('dob', '1990-01-01')
            ->set('gender', 'Male')
            ->set('address', 'Test Address')
            ->set('bvn', '12345678901')
            ->set('nin', '12345678901')
            ->set('bank_name', 'Bank')
            ->set('account_number', '1234567890')
            ->set('bank_account_name', 'John Borrower')
            ->set('next_of_kin_name', 'Kin')
            ->set('next_of_kin_relationship', 'Brother')
            ->set('next_of_kin_phone', '08055556666')
            ->set('marital_status', 'Single')
            ->set('dependents', 0)
            ->set('passport_photo', UploadedFile::fake()->create('passport.jpg', 100))
            ->set('identity_document', UploadedFile::fake()->create('id.pdf', 100))
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('transactions', [
            'type' => 'registration_fee',
            'amount' => 100000, // 1000 Naira
            'organization_id' => $org->id,
        ]);
    }

    #[Test]
    public function it_records_loan_disbursement_transaction()
    {
        $org = Organization::factory()->create();
        app(TenantSession::class)->setTenantId($org->id);

        $admin = User::factory()->create(['organization_id' => $org->id, 'type' => 'admin']);

        $user = User::factory()->create(['organization_id' => $org->id]);
        $borrower = Borrower::factory()->create(['user_id' => $user->id, 'organization_id' => $org->id]);

        $loan = Loan::factory()->create([
            'organization_id' => $org->id,
            'borrower_id' => $borrower->id,
            'amount' => 5000,
            'status' => 'approved',
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

    #[Test]
    public function it_records_repayment_transaction_via_observer()
    {
        $org = Organization::factory()->create();
        app(TenantSession::class)->setTenantId($org->id);

        $admin = User::factory()->create(['organization_id' => $org->id, 'type' => 'admin']);

        $user = User::factory()->create(['organization_id' => $org->id]);
        $borrower = Borrower::factory()->create(['user_id' => $user->id, 'organization_id' => $org->id]);

        $loan = Loan::factory()->create([
            'organization_id' => $org->id,
            'borrower_id' => $borrower->id,
            'amount' => 5000,
            'status' => 'active',
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

    #[Test]
    public function it_records_savings_deposit_transaction_via_observer()
    {
        $org = Organization::factory()->create();
        app(TenantSession::class)->setTenantId($org->id);

        $admin = User::factory()->create(['organization_id' => $org->id, 'type' => 'admin']);

        $user = User::factory()->create(['organization_id' => $org->id]);
        $account = SavingsAccount::factory()->create([
            'user_id' => $user->id,
            'organization_id' => $org->id,
        ]);

        Auth::login($admin);

        SavingsTransaction::create([
            'savings_account_id' => $account->id,
            'type' => 'deposit',
            'amount' => 2000,
            'payment_method' => 'bank_transfer',
            'transaction_date' => now(),
            'staff_id' => $admin->id,
            'reference' => 'DEP-123',
        ]);

        $this->assertDatabaseHas('transactions', [
            'type' => 'deposit',
            'amount' => 200000,
            'payment_method' => 'bank_transfer',
            'user_id' => $user->id,
            'organization_id' => $org->id,
        ]);
    }

    #[Test]
    public function it_does_not_duplicate_transactions_for_extra_repayment_savings()
    {
        $org = Organization::factory()->create();
        app(TenantSession::class)->setTenantId($org->id);

        $admin = User::factory()->create(['organization_id' => $org->id, 'type' => 'admin']);

        $user = User::factory()->create(['organization_id' => $org->id]);
        $borrower = Borrower::factory()->create(['user_id' => $user->id, 'organization_id' => $org->id]);
        $account = SavingsAccount::factory()->create(['user_id' => $user->id, 'organization_id' => $org->id]);

        $loan = Loan::factory()->create([
            'organization_id' => $org->id,
            'borrower_id' => $borrower->id,
            'amount' => 5000,
            'status' => 'active',
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
            'reference' => 'REP-EXTRA',
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
