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
            'A new loan of ₦'.$loan->amount->format()." was applied for by {$borrowerName}",
            'loan',
            $loan
        );

        if ($org && $org->loan_approval_alerts_enabled) {
            SystemLogger::action(
                'Approve Disbursement',
                "Loan #{$loan->loan_number} for ₦".$loan->amount->format().' is pending approval.',
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
                'Your application for a loan of ₦'.$loan->amount->format()." (Loan #{$loan->loan_number}) has been submitted successfully.",
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
            $currency = $loan->amount->getCurrency();
            $diffMinor = 0;

            if ($loan->wasChanged('processing_fee')) {
                /** @var \App\ValueObjects\Money $newProc */
                $newProc = $loan->processing_fee ?? new \App\ValueObjects\Money(0, $currency);
                /** @var \App\ValueObjects\Money $oldProc */
                $oldProc = $loan->getOriginal('processing_fee') ?? new \App\ValueObjects\Money(0, $currency);
                $diffMinor += ($newProc->getMinorAmount() - $oldProc->getMinorAmount());
            }

            if ($loan->wasChanged('insurance_fee')) {
                /** @var \App\ValueObjects\Money $newIns */
                $newIns = $loan->insurance_fee ?? new \App\ValueObjects\Money(0, $currency);
                /** @var \App\ValueObjects\Money $oldIns */
                $oldIns = $loan->getOriginal('insurance_fee') ?? new \App\ValueObjects\Money(0, $currency);
                $diffMinor += ($newIns->getMinorAmount() - $oldIns->getMinorAmount());
            }

            if ($diffMinor != 0) {
                // Find next unpaid schedule
                /** @var \App\Models\ScheduledRepayment|null $nextSchedule */
                $nextSchedule = $loan->scheduledRepayments()
                    ->whereIn('status', ['applied', 'partial', 'overdue'])
                    ->orderBy('due_date')
                    ->first();

                if ($nextSchedule) {
                    $nextSchedule->update([
                        'penalty_amount' => $nextSchedule->penalty_amount->add(new \App\ValueObjects\Money($diffMinor, $currency)),
                    ]);
                }
            }

            $diffMoney = new \App\ValueObjects\Money($diffMinor, $currency);

            SystemLogger::log(
                'Loan Fees Updated',
                "Fee configuration for Loan #{$loan->loan_number} has been updated. Adjustment of ₦".$diffMoney->format().' applied to next schedule.',
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
