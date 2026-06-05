<?php

namespace App\Observers;

use App\Events\DashboardUpdated;
use App\Events\LoanRepaymentReceived;
use App\Helpers\SystemLogger;
use App\Livewire\AdminDashboard;
use App\Livewire\LoanDashboard;
use App\Livewire\Reports;
use App\Models\Repayment;
use App\Models\SavingsTransaction;
use App\Services\TransactionService;

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
                'Your repayment of ₦'.$repayment->amount->format()." for Loan #{$loan->loan_number} has been recorded.",
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
                'A repayment of ₦'.$repayment->amount->format()." has been recorded for Loan #{$loan->loan_number} ({$borrowerName}).",
                'repayment',
                $repayment,
                false,
                null,
                'low',
                null // ORGANIZATIONAL BROADCAST
            );
        }

        LoanRepaymentReceived::dispatch($loan, $repayment);

        // Record Global Transaction
        TransactionService::record(
            type: 'repayment',
            amount: $repayment->amount,
            user: $loan->borrower->user,
            related: $repayment,
            paymentMethod: $repayment->payment_method,
            notes: "Repayment for Loan #{$loan->loan_number}"
        );

        DashboardUpdated::dispatch($repayment->loan->organization_id);
        LoanDashboard::clearCache($repayment->loan->organization_id);
        AdminDashboard::clearCache($repayment->loan->organization_id, $repayment->loan->portfolio_id);
        Reports::clearCache($repayment->loan->organization_id);
    }

    /**
     * Handle the Repayment "updated" event.
     */
    public function updated(Repayment $repayment): void
    {
        LoanRepaymentReceived::dispatch($repayment->loan, $repayment);
        DashboardUpdated::dispatch($repayment->loan->organization_id);
        LoanDashboard::clearCache($repayment->loan->organization_id);
        AdminDashboard::clearCache($repayment->loan->organization_id, $repayment->loan->portfolio_id);
        Reports::clearCache($repayment->loan->organization_id);
    }

    /**
     * Handle the Repayment "deleted" event.
     */
    public function deleted(Repayment $repayment): void
    {
        LoanRepaymentReceived::dispatch($repayment->loan, null);
        DashboardUpdated::dispatch($repayment->loan->organization_id);
        LoanDashboard::clearCache($repayment->loan->organization_id);
        AdminDashboard::clearCache($repayment->loan->organization_id, $repayment->loan->portfolio_id);
        Reports::clearCache($repayment->loan->organization_id);
    }

    /**
     * Handle the Repayment "deleting" event.
     */
    public function deleting(Repayment $repayment): void
    {
        // When a repayment is deleted, we must also remove the savings transaction
        // that was created from its extra_amount.
        foreach ($repayment->savingsTransactions as $transaction) {
            /** @var SavingsTransaction $transaction */
            $account = $transaction->savingsAccount;
            $account->update([
                'balance' => $account->balance->subtract($transaction->amount),
            ]);
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
