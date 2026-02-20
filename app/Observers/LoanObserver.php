<?php

namespace App\Observers;

use App\Helpers\SystemLogger;
use App\Models\Loan;

class LoanObserver
{
    /**
     * Handle the Loan "created" event.
     */
    public function created(Loan $loan): void
    {
        $org = $loan->organization;

        SystemLogger::success(
            'New Loan Application',
            'A new loan of ₦'.number_format($loan->amount).' was applied for by '.($loan->borrower->user->name ?? 'a borrower'),
            'loan',
            $loan
        );

        if ($org && $org->loan_approval_alerts_enabled) {
            SystemLogger::action(
                'Approve Disbursement',
                "Loan #{$loan->loan_number} for ₦".number_format($loan->amount).' is pending approval.',
                route('loan.show', $loan->id, false),
                'loan',
                $loan,
                'high'
            );
        }

        // Notify the Borrower
        SystemLogger::success(
            'Application Submitted',
            'Your application for a loan of ₦'.number_format($loan->amount)." (Loan #{$loan->loan_number}) has been submitted successfully.",
            'loan',
            $loan,
            false,
            null,
            'low',
            $loan->borrower->user_id
        );
    }

    /**
     * Handle the Loan "updated" event.
     */
    public function updated(Loan $loan): void
    {
        if ($loan->wasChanged('status')) {
            $status = $loan->status;
            $title = 'Loan Status Updated';
            $type = 'info';

            match ($status) {
                'approved' => [$title = 'Loan Approved', $type = 'success'],
                'active' => [$title = 'Loan Disbursed', $type = 'success'],
                'overdue' => [$title = 'Loan Overdue', $type = 'danger'],
                'repaid' => [$title = 'Loan Fully Repaid', $type = 'success'],
                default => null,
            };

            // 1. Notify the Borrower
            SystemLogger::log(
                $title,
                "Your Loan #{$loan->loan_number} is now ".strtoupper($status),
                $type,
                'loan',
                $loan,
                false,
                null,
                'low',
                $loan->borrower->user_id // RECIPIENT
            );

            // 2. Notify the Organization Staff
            SystemLogger::log(
                $title,
                "Loan #{$loan->loan_number} for {$loan->borrower->user->name} is now ".strtoupper($status),
                $type,
                'loan',
                $loan,
                false,
                null,
                'low',
                null // ORGANIZATIONAL BROADCAST
            );
        }

        // Check for Fee/Penalty Updates
        $feeColumns = [
            'processing_fee',
            'insurance_fee',
            'penalty_value',
            'penalty_type',
            'penalty_frequency',
            'override_system_penalty',
        ];

        if ($loan->wasChanged($feeColumns)) {
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
        if ($loan->wasChanged($termColumns)) {
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
