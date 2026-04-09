<?php

namespace App\Observers;

use App\Helpers\SystemLogger;
use App\Models\Collateral;

class CollateralObserver
{
    /**
     * Handle the Collateral "created" event.
     */
    public function created(Collateral $collateral): void
    {
        $context = $collateral->loan
            ? "to Loan #{$collateral->loan->loan_number}"
            : 'as Company Asset';

        /** @var \App\ValueObjects\Money $value */
        $value = $collateral->value;
        SystemLogger::success(
            'New Collateral Added',
            "Collateral '{$collateral->name}' (₦".$value->format().") has been added {$context}.",
            'collateral',
            $collateral
        );

        \App\Events\DashboardUpdated::dispatch($collateral->organization_id);
        \App\Livewire\LoanDashboard::clearCache($collateral->organization_id);
        \App\Livewire\AdminDashboard::clearCache($collateral->organization_id);
        \App\Livewire\Reports::clearCache($collateral->organization_id);
    }

    /**
     * Handle the Collateral "updated" event.
     */
    public function updated(Collateral $collateral): void
    {
        if ($collateral->isDirty(['name', 'value', 'status', 'condition'])) {
            $context = $collateral->loan
                ? "on Loan #{$collateral->loan->loan_number}"
                : 'Company Asset';

            SystemLogger::log(
                'Collateral Updated',
                "Details for collateral '{$collateral->name}' ({$context}) have been updated.",
                'info',
                'collateral',
                $collateral
            );
        }

        \App\Events\DashboardUpdated::dispatch($collateral->organization_id);
        \App\Livewire\LoanDashboard::clearCache($collateral->organization_id);
        \App\Livewire\AdminDashboard::clearCache($collateral->organization_id);
        \App\Livewire\Reports::clearCache($collateral->organization_id);
    }

    /**
     * Handle the Collateral "deleted" event.
     */
    public function deleted(Collateral $collateral): void
    {
        $context = $collateral->loan
            ? "from Loan #{$collateral->loan->loan_number}"
            : '';

        SystemLogger::warning(
            'Collateral Removed',
            "Collateral '{$collateral->name}' has been removed {$context}.",
            'collateral',
            $collateral
        );

        \App\Events\DashboardUpdated::dispatch($collateral->organization_id);
        \App\Livewire\LoanDashboard::clearCache($collateral->organization_id);
        \App\Livewire\AdminDashboard::clearCache($collateral->organization_id);
        \App\Livewire\Reports::clearCache($collateral->organization_id);
    }

    /**
     * Handle the Collateral "restored" event.
     */
    public function restored(Collateral $collateral): void
    {
        //
    }

    /**
     * Handle the Collateral "force deleted" event.
     */
    public function forceDeleted(Collateral $collateral): void
    {
        //
    }
}
