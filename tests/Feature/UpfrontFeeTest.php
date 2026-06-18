<?php

namespace Tests\Feature;

use App\DTOs\LoanApplicationDTO;
use App\Models\Borrower;
use App\Models\Organization;
use App\Models\Portfolio;
use App\Models\User;
use App\Services\CashbookService;
use App\Services\LoanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpfrontFeeTest extends TestCase
{
    use RefreshDatabase;

    protected $org;

    protected $user;

    protected $borrower;

    protected $portfolio;

    protected function setUp(): void
    {
        parent::setUp();

        $this->org = Organization::factory()->create(['currency_code' => 'NGN']);
        $this->user = User::factory()->create(['organization_id' => $this->org->id]);
        $this->actingAs($this->user);

        $this->borrower = Borrower::factory()->create(['organization_id' => $this->org->id]);
        $this->portfolio = Portfolio::factory()->create(['organization_id' => $this->org->id]);
    }

    public function test_loan_creation_generates_upfront_fee_transactions()
    {
        $loanService = app(LoanService::class);

        $data = [
            'borrower_id' => $this->borrower->id,
            'loan_product' => 'Personal Loan',
            'amount' => 100000.00, // 100k
            'interest_rate' => 10,
            'interest_calculation_type' => 'percentage',
            'interest_type' => 'month',
            'duration' => 12,
            'duration_unit' => 'months',
            'repayment_cycle' => 'monthly',
            'num_repayments' => 12,
            'processing_fee' => 5000.00, // 5k fixed
            'processing_fee_type' => 'fixed',
            'insurance_fee' => 2.5, // 2.5%
            'insurance_fee_type' => 'percentage',
            'portfolio_id' => $this->portfolio->id,
            'loan_number' => 'LN-1001',
            'release_date' => now()->toDateString(),
        ];

        $dto = LoanApplicationDTO::fromArray($data);
        $loan = $loanService->createLoan($dto);

        // 1. Verify upfront transactions
        $this->assertDatabaseHas('transactions', [
            'organization_id' => $this->org->id,
            'type' => 'processing_fee',
            'amount' => 500000, // 5000.00 * 100
            'related_id' => $loan->id,
        ]);

        $this->assertDatabaseHas('transactions', [
            'organization_id' => $this->org->id,
            'type' => 'insurance_fee',
            'amount' => 250000, // 2.5% of 100k = 2500.00 -> 250000 minor
            'related_id' => $loan->id,
        ]);

        // 2. Verify repayment schedule excludes fees
        $firstSchedule = $loan->scheduledRepayments()->where('installment_number', 1)->first();
        // Principal = 100k / 12 = 8333.33 -> 833333 minor
        $this->assertEquals(833333, $firstSchedule->principal_amount->getMinorAmount());
        // Penalty (Fee) should be 0
        $this->assertEquals(0, $firstSchedule->penalty_amount->getMinorAmount());

        // 3. Verify Cashbook picks up the fees
        $cashbookService = app(CashbookService::class);
        $entry = $cashbookService->getEntryForDate(now(), $this->org);
        $cashbookService->fetchSystemData($entry);

        $this->assertEquals(500000, $entry->loan_processing_fees->getMinorAmount());
        $this->assertEquals(250000, $entry->insurance_fees->getMinorAmount());

        // 4. Verify Expected Bank Transfers includes these fees
        // (Assuming no other transactions, only these 2 upfront fees)
        $this->assertEquals(750000, $entry->expected_bank_transfers->getMinorAmount());
    }
}
