<?php

namespace Tests\Unit;

use App\Models\Borrower;
use App\Models\Loan;
use App\Models\Repayment;
use App\Models\ScheduledRepayment;
use App\Services\TrustScoringService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrustScoreCalculationTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_0_for_borrowers_with_no_schedules()
    {
        $borrower = Borrower::factory()->create();
        $this->assertEquals(0, TrustScoringService::calculate($borrower));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_calculates_perfect_score_for_on_time_payments()
    {
        $borrower = Borrower::factory()->create();
        $loan = Loan::factory()->create(['borrower_id' => $borrower->id, 'amount' => 1000]);

        $schedule = ScheduledRepayment::create([
            'loan_id' => $loan->id,
            'due_date' => now()->subDays(5),
            'principal_amount' => 1000,
            'interest_amount' => 0,
            'installment_number' => 1,
            'status' => 'paid',
            'paid_amount' => 1000,
        ]);

        Repayment::create([
            'loan_id' => $loan->id,
            'amount' => 1000,
            'paid_at' => now()->subDays(6), // Early
        ]);

        $this->assertEquals(100, TrustScoringService::calculate($borrower));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_penalizes_late_payments()
    {
        $borrower = Borrower::factory()->create();
        $loan = Loan::factory()->create([
            'borrower_id' => $borrower->id,
            'amount' => 1000,
            'interest_rate' => 0, // Ensure no interest so it stays repaid when 1000 is paid
            'status' => 'repaid',
        ]);

        $schedule = ScheduledRepayment::create([
            'loan_id' => $loan->id,
            'due_date' => now()->subDays(10),
            'principal_amount' => 1000,
            'interest_amount' => 0,
            'installment_number' => 1,
            'status' => 'paid',
            'paid_amount' => 1000,
        ]);

        Repayment::create([
            'loan_id' => $loan->id,
            'amount' => 1000,
            'principal_amount' => 1000,
            'interest_amount' => 0,
            'paid_at' => now()->subDays(2), // 8 days late (0.5 multiplier)
        ]);

        // Expected: (1000 * 0.5) / 1000 * 100 = 50
        // repaidCount = 1, repaidCount * 2 = 2. Repayment::count() = 1. 1 > 2 = false. Bonus = 0.
        // Wait, score = 50 + 2 (repaidCount bonus) = 52.
        $this->assertEquals(52, TrustScoringService::calculate($borrower));
    }
}
