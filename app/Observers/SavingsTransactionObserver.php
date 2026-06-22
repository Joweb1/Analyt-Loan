<?php

namespace App\Observers;

use App\Models\SavingsTransaction;
use App\Models\Transaction;
use App\Services\TransactionService;
use App\ValueObjects\Money;

class SavingsTransactionObserver
{
    /**
     * Handle the SavingsTransaction "created" event.
     */
    public function created(SavingsTransaction $savingsTransaction): void
    {
        (new CashbookUnlockObserver)->handle($savingsTransaction->transaction_date->toDateString(), $savingsTransaction->savingsAccount->organization_id);

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
        (new CashbookUnlockObserver)->handle($savingsTransaction->transaction_date->toDateString(), $savingsTransaction->savingsAccount->organization_id);

        if ($savingsTransaction->wasChanged('amount')) {
            $oldAmountMinor = (int) $savingsTransaction->getRawOriginal('amount');
            $newAmountMinor = $savingsTransaction->amount->getMinorAmount();
            $differenceMinor = $newAmountMinor - $oldAmountMinor;

            // Find the original transaction in the master ledger
            $originalTransaction = Transaction::where('related_id', $savingsTransaction->id)
                ->where('related_type', get_class($savingsTransaction))
                ->whereIn('type', ['deposit', 'withdrawal'])
                ->whereNull('parent_id')
                ->first();

            if ($originalTransaction) {
                $difference = new Money($differenceMinor, $savingsTransaction->amount->getCurrency());

                TransactionService::record(
                    type: 'adjustment',
                    amount: $difference,
                    user: $savingsTransaction->savingsAccount->user,
                    related: $savingsTransaction,
                    notes: 'Adjustment for Savings Transaction update. Original: ₦'.(new Money($oldAmountMinor, $savingsTransaction->amount->getCurrency()))->format().', New: ₦'.$savingsTransaction->amount->format(),
                    parentId: $originalTransaction->id
                );
            }
        }
    }

    /**
     * Handle the SavingsTransaction "deleted" event.
     */
    public function deleted(SavingsTransaction $savingsTransaction): void
    {
        (new CashbookUnlockObserver)->handle($savingsTransaction->transaction_date->toDateString(), $savingsTransaction->savingsAccount->organization_id);
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
