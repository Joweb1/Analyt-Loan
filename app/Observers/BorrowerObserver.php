<?php

namespace App\Observers;

use App\Helpers\SystemLogger;
use App\Models\Borrower;

class BorrowerObserver
{
    /**
     * Handle the Borrower "created" event.
     */
    public function created(Borrower $borrower): void
    {
        $org = $borrower->organization;

        SystemLogger::success(
            'New Customer Registered',
            "Customer {$borrower->user->name} has been successfully registered in the system.",
            'borrower',
            $borrower
        );

        if ($org && $org->new_borrower_notifications_enabled) {
            SystemLogger::action(
                'KYC Review Required',
                "New customer {$borrower->user->name} registered. Please verify documents.",
                route('borrower.profile', $borrower->id, false),
                'kyc',
                $borrower,
                'medium'
            );
        }

        \App\Events\DashboardUpdated::dispatch($borrower->organization_id);
        \App\Livewire\LoanDashboard::clearCache($borrower->organization_id);
        \App\Livewire\AdminDashboard::clearCache($borrower->organization_id);
        \App\Livewire\Reports::clearCache($borrower->organization_id);
    }

    /**
     * Handle the Borrower "updated" event.
     */
    public function updated(Borrower $borrower): void
    {
        // Detect KYC Submission (status changed to pending)
        if ($borrower->wasChanged('kyc_status') && $borrower->kyc_status === 'pending') {
            SystemLogger::action(
                'KYC Submitted for Review',
                "Customer {$borrower->user->name} has submitted their KYC details and is awaiting verification.",
                route('borrower.profile', $borrower->id, false),
                'kyc',
                $borrower,
                'medium'
            );
        }

        // Also notify if status is already pending but fields are updated (completing profile)
        $kycFields = [
            'bvn', 'national_identity_number', 'identity_document',
            'passport_photograph', 'bank_statement', 'income_proof',
            'address', 'date_of_birth',
        ];

        if ($borrower->kyc_status === 'pending' && $borrower->wasChanged($kycFields) && ! $borrower->wasChanged('kyc_status')) {
            SystemLogger::action(
                'KYC Profile Updated',
                "Customer {$borrower->user->name} has updated their pending KYC details.",
                route('borrower.profile', $borrower->id, false),
                'kyc',
                $borrower,
                'medium'
            );
        }

        if ($borrower->wasChanged('kyc_status')) {
            if ($borrower->kyc_status === 'approved') {
                SystemLogger::success(
                    'KYC Approved',
                    'Your KYC verification was successful. You can now apply for loans.',
                    'kyc',
                    $borrower,
                    false,
                    null,
                    'high',
                    $borrower->user_id // Notify the borrower
                );
            } elseif ($borrower->kyc_status === 'rejected') {
                SystemLogger::danger(
                    'KYC Rejected',
                    'Your KYC verification was rejected. Please check your details and resubmit.',
                    'kyc',
                    $borrower,
                    false,
                    null,
                    'high',
                    $borrower->user_id // Notify the borrower
                );
            }
        }

        \App\Events\DashboardUpdated::dispatch($borrower->organization_id);
        \App\Livewire\LoanDashboard::clearCache($borrower->organization_id);
        \App\Livewire\AdminDashboard::clearCache($borrower->organization_id);
        \App\Livewire\Reports::clearCache($borrower->organization_id);
    }

    /**
     * Handle the Borrower "deleted" event.
     */
    public function deleted(Borrower $borrower): void
    {
        \App\Events\DashboardUpdated::dispatch($borrower->organization_id);
        \App\Livewire\LoanDashboard::clearCache($borrower->organization_id);
        \App\Livewire\AdminDashboard::clearCache($borrower->organization_id);
        \App\Livewire\Reports::clearCache($borrower->organization_id);
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
