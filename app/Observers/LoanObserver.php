<?php

namespace App\Observers;

use App\Models\Loan;
use App\Helpers\SystemLogger;

class LoanObserver
{
    /**
     * Handle the Loan "created" event.
     */
    public function created(Loan $loan): void
    {
        SystemLogger::success(
            'New Loan Application',
            "A new loan of ₦" . number_format($loan->amount) . " was applied for by " . ($loan->borrower->user->name ?? 'a borrower'),
            'loan',
            $loan
        );

        SystemLogger::action(
            'Approve Disbursement',
            "Loan #{$loan->loan_number} for ₦" . number_format($loan->amount) . " is pending approval.",
            route('loan.show', $loan->id, false),
            'loan',
            $loan,
            'high'
        );
    }

    /**
     * Handle the Loan "updated" event.
     */
    public function updated(Loan $loan): void
    {
        if ($loan->isDirty('status')) {
            $status = $loan->status;
            $title = "Loan Status Updated";
            $type = 'info';

            match($status) {
                'approved' => [$title = "Loan Approved", $type = 'success'],
                'active' => [$title = "Loan Disbursed", $type = 'success'],
                'overdue' => [$title = "Loan Overdue", $type = 'danger'],
                'repaid' => [$title = "Loan Fully Repaid", $type = 'success'],
                default => null,
            };

            SystemLogger::log(
                $title,
                "Loan #{$loan->loan_number} for {$loan->borrower->user->name} is now " . strtoupper($status),
                $type,
                'loan',
                $loan
            );
        }

        // Check for Fee/Penalty Updates
        $feeColumns = [
            'processing_fee', 
            'insurance_fee', 
            'penalty_value', 
            'penalty_type', 
            'penalty_frequency', 
            'override_system_penalty'
        ];

        if ($loan->isDirty($feeColumns)) {
            SystemLogger::log(
                'Loan Fees Updated',
                "Fee or penalty configuration for Loan #{$loan->loan_number} has been updated.",
                'info',
                'loan',
                $loan
            );
        }

        // Check for General Terms Updates
        $termColumns = ['amount', 'interest_rate', 'duration', 'repayment_cycle', 'loan_product', 'release_date'];
        if ($loan->isDirty($termColumns)) {
            SystemLogger::log(
                'Loan Terms Updated',
                "Key terms (Amount, Rate, Duration, etc.) for Loan #{$loan->loan_number} have been modified.",
                'info',
                'loan',
                $loan
            );
        }
    }

    /**
     * Handle the Loan "deleted" event.
     */
    public function deleted(Loan $loan): void
    {
        SystemLogger::log(
            'Loan Deleted',
            "Loan #{$loan->loan_number} for {$loan->borrower->user->name} has been permanently deleted.",
            'danger',
            'loan',
            null // Subject is null as it's deleted
        );
    }

    /**
     * Handle the Loan "restored" event.
     */
    public function restored(Loan $loan): void
    {
        //
    }

    /**
     * Handle the Loan "force deleted" event.
     */
    public function forceDeleted(Loan $loan): void
    {
        //
    }
}
