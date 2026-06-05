<?php

namespace App\Observers;

use App\Models\SavingsTransaction;
use App\Services\TransactionService;

class SavingsTransactionObserver
{
    /**
     * Handle the SavingsTransaction "created" event.
     */
    public function created(SavingsTransaction $savingsTransaction): void
    {
        // Don't duplicate if it's already linked to a repayment (RepaymentObserver handles that)
        if ($savingsTransaction->repayment_id) {
            return;
        }

        TransactionService::record(
            type: $savingsTransaction->type,
            amount: $savingsTransaction->amount,
            user: $savingsTransaction->savingsAccount->user,
            related: $savingsTransaction,
            paymentMethod: $savingsTransaction->payment_method,
            notes: $savingsTransaction->notes
        );
    }

    /**
     * Handle the SavingsTransaction "updated" event.
     */
    public function updated(SavingsTransaction $savingsTransaction): void
    {
        //
    }

    /**
     * Handle the SavingsTransaction "deleted" event.
     */
    public function deleted(SavingsTransaction $savingsTransaction): void
    {
        //
    }

    /**
     * Handle the SavingsTransaction "restored" event.
     */
    public function restored(SavingsTransaction $savingsTransaction): void
    {
        //
    }

    /**
     * Handle the SavingsTransaction "force deleted" event.
     */
    public function forceDeleted(SavingsTransaction $savingsTransaction): void
    {
        //
    }
}
