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
        $borrowerName = $loan->borrower->user->name ?? 'a borrower';

        SystemLogger::success(
            'New Loan Application',
            'A new loan of ₦'.number_format($loan->amount)." was applied for by {$borrowerName}",
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
        if ($loan->borrower && $loan->borrower->user_id) {
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

        \App\Events\DashboardUpdated::dispatch($loan->organization_id);
        \App\Livewire\LoanDashboard::clearCache($loan->organization_id);
        \App\Livewire\AdminDashboard::clearCache($loan->organization_id, $loan->portfolio_id);
        \App\Livewire\Reports::clearCache($loan->organization_id);
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
            if ($loan->borrower && $loan->borrower->user_id) {
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
            }

            // 2. Notify the Organization Staff
            $borrowerName = $loan->borrower->user->name ?? 'a borrower';
            SystemLogger::log(
                $title,
                "Loan #{$loan->loan_number} for {$borrowerName} is now ".strtoupper($status),
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
        ];

        if ($loan->wasChanged($feeColumns) && in_array($loan->status, ['active', 'overdue', 'approved'])) {
            $diff = 0;
            if ($loan->wasChanged('processing_fee')) {
                $diff += (float) $loan->processing_fee - (float) $loan->getOriginal('processing_fee');
            }
            if ($loan->wasChanged('insurance_fee')) {
                $diff += (float) $loan->insurance_fee - (float) $loan->getOriginal('insurance_fee');
            }

            if ($diff != 0) {
                // Find next unpaid schedule
                $nextSchedule = $loan->scheduledRepayments()
                    ->whereIn('status', ['applied', 'partial', 'overdue'])
                    ->orderBy('due_date')
                    ->first();

                if ($nextSchedule) {
                    $nextSchedule->increment('penalty_amount', $diff);
                }
            }

            SystemLogger::log(
                'Loan Fees Updated',
                "Fee configuration for Loan #{$loan->loan_number} has been updated. Adjustment of ₦".number_format($diff, 2).' applied to next schedule.',
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

        \App\Events\DashboardUpdated::dispatch($loan->organization_id);
        \App\Livewire\LoanDashboard::clearCache($loan->organization_id);
        \App\Livewire\AdminDashboard::clearCache($loan->organization_id, $loan->portfolio_id);
        \App\Livewire\Reports::clearCache($loan->organization_id);
    }

    /**
     * Handle the Loan "deleted" event.
     */
    public function deleted(Loan $loan): void
    {
        $borrowerName = $loan->borrower->user->name ?? 'a borrower';
        SystemLogger::log(
            'Loan Deleted',
            "Loan #{$loan->loan_number} for {$borrowerName} has been permanently deleted.",
            'danger',
            'loan',
            null // Subject is null as it's deleted
        );

        \App\Events\DashboardUpdated::dispatch($loan->organization_id);
        \App\Livewire\LoanDashboard::clearCache($loan->organization_id);
        \App\Livewire\AdminDashboard::clearCache($loan->organization_id, $loan->portfolio_id);
        \App\Livewire\Reports::clearCache($loan->organization_id);
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
