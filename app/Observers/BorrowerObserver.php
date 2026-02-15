<?php

namespace App\Observers;

use App\Models\Borrower;
use App\Helpers\SystemLogger;

class BorrowerObserver
{
    /**
     * Handle the Borrower "created" event.
     */
    public function created(Borrower $borrower): void
    {
        SystemLogger::success(
            'New Customer Registered',
            "Customer {$borrower->user->name} has been successfully registered in the system.",
            'borrower',
            $borrower
        );

        SystemLogger::action(
            'KYC Review Required',
            "New customer {$borrower->user->name} registered. Please verify documents.",
            route('customer', [], false),
            'kyc',
            $borrower,
            'medium'
        );
    }

    /**
     * Handle the Borrower "updated" event.
     */
    public function updated(Borrower $borrower): void
    {
        if ($borrower->isDirty('bvn') || $borrower->isDirty('national_identity_number')) {
            SystemLogger::success(
                'Customer KYC Updated',
                "KYC documents for {$borrower->user->name} have been updated/verified.",
                'borrower',
                $borrower
            );
        }
    }

    /**
     * Handle the Borrower "deleted" event.
     */
    public function deleted(Borrower $borrower): void
    {
        //
    }

    /**
     * Handle the Borrower "restored" event.
     */
    public function restored(Borrower $borrower): void
    {
        //
    }

    /**
     * Handle the Borrower "force deleted" event.
     */
    public function forceDeleted(Borrower $borrower): void
    {
        //
    }
}
