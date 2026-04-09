<?php

namespace Tests\Feature\Services;

use App\Models\Borrower;
use App\Models\Collateral;
use App\Models\Loan;
use App\Models\Organization;
use App\Models\User;
use App\Services\LoanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LoanServiceTest extends TestCase
{
    use RefreshDatabase;

    protected LoanService $loanService;

    protected User $user;

    protected Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();

        \Illuminate\Support\Facades\Event::fake([
            \App\Events\DashboardUpdated::class,
        ]);

        $this->loanService = new LoanService;
        $this->organization = Organization::factory()->create();
        $this->user = User::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Admin']);
        $this->user->assignRole('Admin');

        $this->actingAs($this->user);
    }

    public function test_it_can_create_a_loan()
    {
        $borrower = Borrower::factory()->create(['organization_id' => $this->organization->id]);

        $data = [
            'borrower_id' => $borrower->id,
            'loan_number' => 'LN-TEST-001',
            'amount' => 1000.0, // 1,000 Major = 100,000 Minor
            'loan_product' => 'Personal Loan',
            'interest_rate' => 10,
            'interest_type' => 'year',
            'duration' => 6,
            'duration_unit' => 'month',
            'repayment_cycle' => 'monthly',
            'num_repayments' => 6,
        ];

        $dto = \App\DTOs\LoanApplicationDTO::fromArray($data);
        $loan = $this->loanService->createLoan($dto);

        $this->assertInstanceOf(Loan::class, $loan);
        $loan->refresh();
        $this->assertEquals('approved', $loan->status); // Admin (actingAs) creates approved loan
        $this->assertDatabaseHas('loans', [
            'id' => $loan->id,
            'loan_number' => 'LN-TEST-001',
            'organization_id' => $this->organization->id,
            'status' => 'approved',
            'amount' => 100000,
        ]);
    }

    public function test_it_can_create_a_loan_with_attachment()
    {
        config(['queue.default' => 'sync']);
        config(['filesystems.disks.supabase.is_configured' => false]);
        Storage::fake('local');
        Storage::fake('public');
        Storage::fake('supabase');

        $borrower = Borrower::factory()->create(['organization_id' => $this->organization->id]);
        $file = UploadedFile::fake()->create('contract.pdf', 100);

        $data = [
            'borrower_id' => $borrower->id,
            'loan_number' => 'LN-ATTACH-001',
            'amount' => 50000,
            'loan_product' => 'Small Loan',
            'interest_rate' => 5,
            'interest_type' => 'month',
            'duration' => 1,
            'duration_unit' => 'month',
            'repayment_cycle' => 'monthly',
            'num_repayments' => 1,
        ];

        $dto = \App\DTOs\LoanApplicationDTO::fromArray($data);
        $loan = $this->loanService->createLoan($dto, $file);

        $loan->refresh();
        $this->assertNotEmpty($loan->attachments);
    }

    public function test_it_links_collateral_on_creation()
    {
        $borrower = Borrower::factory()->create(['organization_id' => $this->organization->id]);
        $collateral = Collateral::factory()->create([
            'organization_id' => $this->organization->id,
            'status' => 'deposited',
            'loan_id' => null,
        ]);

        $data = [
            'borrower_id' => $borrower->id,
            'loan_number' => 'LN-COLL-001',
            'amount' => 100000,
            'loan_product' => 'Secured Loan',
            'interest_rate' => 5,
            'interest_type' => 'month',
            'duration' => 1,
            'duration_unit' => 'month',
            'repayment_cycle' => 'monthly',
            'num_repayments' => 1,
        ];

        $dto = \App\DTOs\LoanApplicationDTO::fromArray($data);
        $loan = $this->loanService->createLoan($dto, null, $collateral->id);

        $this->assertDatabaseHas('collaterals', [
            'id' => $collateral->id,
            'loan_id' => $loan->id,
            'status' => 'in_vault',
        ]);
    }

    public function test_it_can_update_a_loan()
    {
        $loan = Loan::factory()->create(['organization_id' => $this->organization->id]);
        $newData = [
            'borrower_id' => $loan->borrower_id,
            'amount' => 2500.0, // 2,500 Major = 250,000 Minor
            'loan_product' => $loan->loan_product,
            'interest_rate' => $loan->interest_rate,
            'interest_type' => $loan->interest_type,
            'duration' => $loan->duration,
            'duration_unit' => $loan->duration_unit,
            'repayment_cycle' => $loan->repayment_cycle,
            'num_repayments' => $loan->num_repayments,
        ];

        $dto = \App\DTOs\LoanApplicationDTO::fromArray($newData);
        $updatedLoan = $this->loanService->updateLoan($loan, $dto);

        $this->assertEquals(250000, $updatedLoan->amount->getMinorAmount());
        $this->assertDatabaseHas('loans', [
            'id' => $loan->id,
            'amount' => 250000,
        ]);
    }

    public function test_it_activates_loan_without_collateral()
    {
        $borrower = Borrower::factory()->create(['organization_id' => $this->organization->id]);
        $loan = Loan::factory()->create([
            'organization_id' => $this->organization->id,
            'borrower_id' => $borrower->id,
            'amount' => 100000,
            'status' => 'approved',
        ]);

        // No collateral added

        $activatedLoan = $this->loanService->activateLoan($loan);

        $this->assertEquals('active', $activatedLoan->status);
    }

    public function test_it_applies_risk_based_pricing_discount()
    {
        $borrower = Borrower::factory()->create([
            'organization_id' => $this->organization->id,
            'trust_score' => 90, // Excellent
        ]);

        $data = [
            'borrower_id' => $borrower->id,
            'loan_number' => 'LN-DISCOUNT-001',
            'amount' => 100000,
            'loan_product' => 'Dynamic Loan',
            'interest_rate' => 0, // Auto-calculate
            'interest_type' => 'month',
            'duration' => 1,
            'duration_unit' => 'month',
            'repayment_cycle' => 'monthly',
            'num_repayments' => 1,
        ];

        $dto = \App\DTOs\LoanApplicationDTO::fromArray($data);
        $loan = $this->loanService->createLoan($dto);

        // 10% base * 0.8 (20% discount) = 8%
        $this->assertEquals(8, $loan->interest_rate);
    }

    public function test_it_applies_risk_based_pricing_premium()
    {
        $borrower = Borrower::factory()->create([
            'organization_id' => $this->organization->id,
            'trust_score' => 20, // High Risk
        ]);

        $data = [
            'borrower_id' => $borrower->id,
            'loan_number' => 'LN-PREMIUM-001',
            'amount' => 100000,
            'loan_product' => 'Dynamic Loan',
            'interest_rate' => 0, // Auto-calculate
            'interest_type' => 'month',
            'duration' => 1,
            'duration_unit' => 'month',
            'repayment_cycle' => 'monthly',
            'num_repayments' => 1,
        ];

        $dto = \App\DTOs\LoanApplicationDTO::fromArray($data);
        $loan = $this->loanService->createLoan($dto);

        // 10% base * 1.25 (25% premium) = 12.5%
        $this->assertEquals(12.5, $loan->interest_rate);
    }
}
