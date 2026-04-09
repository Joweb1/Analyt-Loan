<?php

namespace Tests\Unit;

use App\Models\Loan;
use App\Models\ScheduledRepayment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class MidnightSyncTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_marks_late_loans_as_overdue()
    {
        $loan = Loan::factory()->create(['status' => 'active']);
        ScheduledRepayment::create([
            'loan_id' => $loan->id,
            'due_date' => now()->subDay(),
            'principal_amount' => 1000,
            'interest_amount' => 0,
            'installment_number' => 1,
            'status' => 'applied',
        ]);

        Artisan::call('app:midnight-sync');

        $this->assertEquals('overdue', $loan->fresh()->status);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_applies_recurring_penalties()
    {
        $loan = Loan::factory()->create([
            'status' => 'active',
            'penalty_value' => 50,
            'penalty_type' => 'fixed',
            'penalty_frequency' => 'daily',
        ]);

        $schedule = ScheduledRepayment::create([
            'loan_id' => $loan->id,
            'due_date' => now()->subDay(),
            'principal_amount' => 1000,
            'interest_amount' => 0,
            'penalty_amount' => 0,
            'installment_number' => 1,
            'status' => 'applied',
        ]);

        Artisan::call('app:midnight-sync');

        $this->assertEquals(5000, $schedule->fresh()->penalty_amount->getMinorAmount());
    }
}
