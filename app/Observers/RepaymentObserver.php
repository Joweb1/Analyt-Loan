<?php

namespace App\Observers;

use App\Events\LoanRepaymentReceived;
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
        if (! $loan) {
            return;
        }

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
                $loan->borrower->user_id ?? null // RECIPIENT
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

        LoanRepaymentReceived::dispatch($loan, $repayment);
        \App\Events\DashboardUpdated::dispatch($repayment->loan->organization_id);
        \App\Livewire\LoanDashboard::clearCache($repayment->loan->organization_id);
        \App\Livewire\AdminDashboard::clearCache($repayment->loan->organization_id, $repayment->loan->portfolio_id);
        \App\Livewire\Reports::clearCache($repayment->loan->organization_id);
    }

    /**
     * Handle the Repayment "updated" event.
     */
    public function updated(Repayment $repayment): void
    {
        LoanRepaymentReceived::dispatch($repayment->loan, $repayment);
        \App\Events\DashboardUpdated::dispatch($repayment->loan->organization_id);
        \App\Livewire\LoanDashboard::clearCache($repayment->loan->organization_id);
        \App\Livewire\AdminDashboard::clearCache($repayment->loan->organization_id, $repayment->loan->portfolio_id);
        \App\Livewire\Reports::clearCache($repayment->loan->organization_id);
    }

    /**
     * Handle the Repayment "deleted" event.
     */
    public function deleted(Repayment $repayment): void
    {
        LoanRepaymentReceived::dispatch($repayment->loan, $repayment);
        \App\Events\DashboardUpdated::dispatch($repayment->loan->organization_id);
        \App\Livewire\LoanDashboard::clearCache($repayment->loan->organization_id);
        \App\Livewire\AdminDashboard::clearCache($repayment->loan->organization_id, $repayment->loan->portfolio_id);
        \App\Livewire\Reports::clearCache($repayment->loan->organization_id);
    }

    /**
     * Handle the Repayment "deleting" event.
     */
    public function deleting(Repayment $repayment): void
    {
        // When a repayment is deleted, we must also remove the savings transaction
        // that was created from its extra_amount.
        foreach ($repayment->savingsTransactions as $transaction) {
            /** @var \App\Models\SavingsTransaction $transaction */
            $account = $transaction->savingsAccount;
            $account->decrement('balance', $transaction->amount);
            $transaction->delete();
        }
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
