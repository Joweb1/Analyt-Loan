<?php

namespace App\Listeners;

use App\Events\LoanRepaymentReceived;

class SyncLoanSchedule
{
    /**
     * Handle the event.
     */
    public function handle(LoanRepaymentReceived $event): void
    {
        $event->loan->refreshRepaymentStatus();
    }
}
