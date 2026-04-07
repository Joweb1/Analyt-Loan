<?php

namespace App\Listeners;

use App\Events\LoanRepaymentReceived;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateBorrowerReadModel implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(LoanRepaymentReceived $event): void
    {
        $borrower = $event->loan->borrower;

        $borrower->update([
            'total_debt' => $borrower->loans()->whereIn('status', ['active', 'overdue'])->sum('amount'),
            'active_loans_count' => $borrower->loans()->whereIn('status', ['active', 'overdue'])->count(),
        ]);
    }
}
