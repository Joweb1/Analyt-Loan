<?php

namespace Tests\Unit;

use App\Models\Borrower;
use App\Models\Loan;
use App\Models\Repayment;
use App\Models\ScheduledRepayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoanRepaymentSyncTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_updates_schedule_status_when_payments_are_made()
    {
        $user = User::factory()->create();
        $borrower = Borrower::factory()->create(['user_id' => $user->id]);
        $loan = Loan::factory()->create([
            'borrower_id' => $borrower->id,
            'amount' => 10000,
            'interest_rate' => 0,
            'num_repayments' => 2,
            'repayment_cycle' => 'monthly',
            'release_date' => now()->subMonths(2),
        ]);

        // Create schedules
        $s1 = ScheduledRepayment::create([
            'loan_id' => $loan->id,
            'due_date' => now()->subMonth(),
            'principal_amount' => 5000,
            'interest_amount' => 0,
            'installment_number' => 1,
            'status' => 'pending',
        ]);

        $s2 = ScheduledRepayment::create([
            'loan_id' => $loan->id,
            'due_date' => now(),
            'principal_amount' => 5000,
            'interest_amount' => 0,
            'installment_number' => 2,
            'status' => 'pending',
        ]);

        // Make partial payment
        $loan->repayments()->create([
            'amount' => 3000,
            'paid_at' => now()->subMonth(),
            'collected_by' => $user->id,
            'payment_method' => 'Cash',
        ]);

        // Observer should have triggered sync automatically

        $this->assertEquals('partial', $s1->fresh()->status);
        $this->assertEquals(300000, $s1->fresh()->paid_amount->getMinorAmount());

        // Complete first payment and start second
        $loan->repayments()->create([
            'amount' => 4000,
            'paid_at' => now(),
            'collected_by' => $user->id,
            'payment_method' => 'Cash',
        ]);

        $this->assertEquals('paid', $s1->fresh()->status);
        $this->assertEquals('partial', $s2->fresh()->status);
        $this->assertEquals(200000, $s2->fresh()->paid_amount->getMinorAmount());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_updates_schedule_status_when_payments_are_updated_or_deleted()
    {
        $user = User::factory()->create();
        $borrower = Borrower::factory()->create(['user_id' => $user->id]);
        $loan = Loan::factory()->create([
            'borrower_id' => $borrower->id,
            'amount' => 10000,
            'interest_rate' => 0,
            'num_repayments' => 2,
            'repayment_cycle' => 'monthly',
            'release_date' => now()->subMonths(2),
        ]);

        // Create schedules
        $s1 = ScheduledRepayment::create([
            'loan_id' => $loan->id,
            'due_date' => now()->subMonth(),
            'principal_amount' => 5000,
            'interest_amount' => 0,
            'installment_number' => 1,
            'status' => 'pending',
        ]);

        $repayment = $loan->repayments()->create([
            'amount' => 5000,
            'paid_at' => now()->subMonth(),
            'collected_by' => $user->id,
            'payment_method' => 'Cash',
        ]);

        $this->assertEquals('paid', $s1->fresh()->status);

        // Update repayment to a smaller amount
        $repayment->update(['amount' => 2000]);

        $this->assertEquals('partial', $s1->fresh()->status);
        $this->assertEquals(200000, $s1->fresh()->paid_amount->getMinorAmount());

        // Delete repayment
        $repayment->delete();

        $this->assertEquals('overdue', $s1->fresh()->status);
        $this->assertEquals(0, $s1->fresh()->paid_amount->getMinorAmount());
    }
}
