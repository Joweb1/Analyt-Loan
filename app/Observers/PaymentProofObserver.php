<?php

namespace App\Observers;

use App\Helpers\SystemLogger;
use App\Models\PaymentProof;

class PaymentProofObserver
{
    /**
     * Handle the PaymentProof "created" event.
     */
    public function created(PaymentProof $paymentProof): void
    {
        $borrowerName = $paymentProof->borrower->user->name ?? 'A borrower';
        $loanNumber = $paymentProof->loan->loan_number ?? 'N/A';

        /** @var \App\ValueObjects\Money $amount */
        $amount = $paymentProof->amount;
        // 1. Notify the Admins (Broadcast)
        SystemLogger::action(
            'New Payment Proof Uploaded',
            "{$borrowerName} uploaded a payment proof of ₦".$amount->format()." for Loan #{$loanNumber}.",
            route('loan.show', $paymentProof->loan_id, false),
            'repayment',
            $paymentProof->loan,
            'medium'
        );

        // 2. Notify the Borrower (Direct)
        SystemLogger::success(
            'Payment Proof Received',
            "Your payment proof for Loan #{$loanNumber} has been received and is being verified.",
            'repayment',
            $paymentProof->loan,
            false,
            null,
            'low',
            $paymentProof->borrower->user_id
        );
    }

    /**
     * Handle the PaymentProof "updated" event.
     */
    public function updated(PaymentProof $paymentProof): void
    {
        //
    }

    /**
     * Handle the PaymentProof "deleted" event.
     */
    public function deleted(PaymentProof $paymentProof): void
    {
        //
    }

    /**
     * Handle the PaymentProof "restored" event.
     */
    public function restored(PaymentProof $paymentProof): void
    {
        //
    }

    /**
     * Handle the PaymentProof "force deleted" event.
     */
    public function forceDeleted(PaymentProof $paymentProof): void
    {
        //
    }
}
