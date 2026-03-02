<?php

namespace Tests\Feature\Services;

use App\Exceptions\CollateralInsufficientException;
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

        $this->loanService = new LoanService;
        $this->organization = Organization::factory()->create();
        $this->user = User::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $this->actingAs($this->user);
    }

    public function test_it_can_create_a_loan()
    {
        $borrower = Borrower::factory()->create(['organization_id' => $this->organization->id]);

        $data = [
            'borrower_id' => $borrower->id,
            'loan_number' => 'LN-TEST-001',
            'amount' => 100000,
            'loan_product' => 'Personal Loan',
            'release_date' => now()->format('Y-m-d'),
            'interest_rate' => 10,
            'interest_type' => 'year',
            'duration' => 6,
            'duration_unit' => 'month',
            'repayment_cycle' => 'monthly',
            'num_repayments' => 6,
        ];

        $loan = $this->loanService->createLoan($data);

        $this->assertInstanceOf(Loan::class, $loan);
        $this->assertDatabaseHas('loans', [
            'id' => $loan->id,
            'loan_number' => 'LN-TEST-001',
            'organization_id' => $this->organization->id,
        ]);
    }

    public function test_it_can_create_a_loan_with_attachment()
    {
        Storage::fake('public');
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

        $loan = $this->loanService->createLoan($data, $file);

        $this->assertNotEmpty($loan->attachments);
        // LoanService currently uses default or supabase disk, in testing it might be using local/public depending on config
        // But the service logic put it in 'loan-attachments/'
        $this->assertNotNull($loan->attachments[0]);
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

        $loan = $this->loanService->createLoan($data, null, $collateral->id);

        $this->assertDatabaseHas('collaterals', [
            'id' => $collateral->id,
            'loan_id' => $loan->id,
            'status' => 'in_vault',
        ]);
    }

    public function test_it_can_update_a_loan()
    {
        $loan = Loan::factory()->create(['organization_id' => $this->organization->id]);
        $newData = ['amount' => 250000];

        $updatedLoan = $this->loanService->updateLoan($loan, $newData);

        $this->assertEquals(250000, $updatedLoan->amount);
        $this->assertDatabaseHas('loans', [
            'id' => $loan->id,
            'amount' => 250000,
        ]);
    }

    public function test_it_activates_loan_with_sufficient_collateral()
    {
        $borrower = Borrower::factory()->create(['organization_id' => $this->organization->id]);
        $loan = Loan::factory()->create([
            'organization_id' => $this->organization->id,
            'borrower_id' => $borrower->id,
            'amount' => 100000,
            'status' => 'approved',
        ]);

        Collateral::factory()->create([
            'organization_id' => $this->organization->id,
            'loan_id' => $loan->id,
            'value' => 60000, // 60% of 100,000
            'status' => 'in_vault',
        ]);

        $activatedLoan = $this->loanService->activateLoan($loan);

        $this->assertEquals('active', $activatedLoan->status);
    }

    public function test_it_throws_exception_on_insufficient_collateral()
    {
        $borrower = Borrower::factory()->create(['organization_id' => $this->organization->id]);
        $loan = Loan::factory()->create([
            'organization_id' => $this->organization->id,
            'borrower_id' => $borrower->id,
            'amount' => 100000,
            'status' => 'approved',
        ]);

        Collateral::factory()->create([
            'organization_id' => $this->organization->id,
            'loan_id' => $loan->id,
            'value' => 40000, // 40% of 100,000
            'status' => 'in_vault',
        ]);

        $this->expectException(CollateralInsufficientException::class);

        $this->loanService->activateLoan($loan);
    }
}
