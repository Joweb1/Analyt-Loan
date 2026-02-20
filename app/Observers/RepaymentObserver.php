<?php

namespace App\Observers;

use App\Helpers\SystemLogger;
use App\Models\Repayment;

class RepaymentObserver
{
    /**
     * Handle the Repayment "created" event.
     */
    public function created(Repayment $repayment): void
    {
        $loan = $repayment->loan;
        $org = $loan->organization;
        $borrowerName = $loan->borrower->user->name ?? 'Borrower';

        if ($org && $org->repayment_notifications_enabled) {
            // 1. Notify the Borrower
            SystemLogger::success(
                'Repayment Received',
                'Your repayment of ₦'.number_format($repayment->amount, 2)." for Loan #{$loan->loan_number} has been recorded.",
                'repayment',
                $repayment,
                false,
                null,
                'low',
                $loan->borrower->user_id // RECIPIENT
            );

            // 2. Notify the Organization Staff
            SystemLogger::success(
                'Repayment Received',
                'A repayment of ₦'.number_format($repayment->amount, 2)." has been recorded for Loan #{$loan->loan_number} ({$borrowerName}).",
                'repayment',
                $repayment,
                false,
                null,
                'low',
                null // ORGANIZATIONAL BROADCAST
            );
        }

        // Trigger Trust Score Recalculation
        $repayment->loan->borrower->recalculateTrustScore();

        // Sync Schedule Statuses
        $repayment->loan->refreshRepaymentStatus();
    }

    /**
     * Handle the Repayment "updated" event.
     */
    public function updated(Repayment $repayment): void
    {
        $repayment->loan->refreshRepaymentStatus();
    }

    /**
     * Handle the Repayment "deleted" event.
     */
    public function deleted(Repayment $repayment): void
    {
        $repayment->loan->refreshRepaymentStatus();
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
