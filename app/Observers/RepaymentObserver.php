<?php

namespace App\Observers;

use App\Models\Repayment;
use App\Helpers\SystemLogger;

class RepaymentObserver
{
    /**
     * Handle the Repayment "created" event.
     */
    public function created(Repayment $repayment): void
    {
        $loan = $repayment->loan;
        $borrowerName = $loan->borrower->user->name ?? 'Borrower';
        
        SystemLogger::success(
            'Repayment Received',
            "A repayment of ₦" . number_format($repayment->amount, 2) . " has been recorded for Loan #{$loan->loan_number} ({$borrowerName}).",
            'repayment',
            $repayment
        );
    }

    /**
     * Handle the Repayment "updated" event.
     */
    public function updated(Repayment $repayment): void
    {
        //
    }

    /**
     * Handle the Repayment "deleted" event.
     */
    public function deleted(Repayment $repayment): void
    {
        //
    }

    /**
     * Handle the Repayment "restored" event.
     */
    public function restored(Repayment $repayment): void
    {
        //
    }

    /**
     * Handle the Repayment "force deleted" event.
     */
    public function forceDeleted(Repayment $repayment): void
    {
        //
    }
}